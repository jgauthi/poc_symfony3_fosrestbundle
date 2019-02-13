<?php

namespace App\Controller;

use App\Entity\Advert;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\{RedirectResponse, StreamedResponse};
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\{ArrayDenormalizer, ObjectNormalizer};
use Symfony\Component\Serializer\Serializer;

class AdminController extends BaseAdminController
{
    public function createNewUserEntity(): UserInterface
    {
        $user = $this->get('fos_user.user_manager')->createUser();
        $user->addRole('ROLE_USER');

        return $user;
    }

    public function updateUserEntity(UserInterface $user): void
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::updateEntity($user);
    }

    public function persistUserEntity(UserInterface $user): void
    {
        $this->get('fos_user.user_manager')->updateUser($user, false);
        parent::persistEntity($user);
    }

    // Change default List request
    public function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null): QueryBuilder
    {
        $response = parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        // Display applications/advertSkill from not archived advert
        if (preg_match('#App\\\Entity\\\(Application|AdvertSkill)#i', $entityClass)) {
            $response
                ->innerJoin('entity.advert', 'advert', 'WITH', 'advert.archived = :archived')
                ->setParameter('archived', 0);
        }

        return $response;
    }

    // Transfer archived advert to publish advert
    public function advertRepublishAction(): RedirectResponse
    {
        $em = $this->getDoctrine()->getManager();
        $id = $this->request->query->get('id');

        $advert = $em->getRepository(Advert::class)->find($id);

        if (!empty($advert) && $advert->getArchived()) {
            $advert->setPublished(true)
                ->setArchived(false);

            $em->persist($advert);
            $em->flush();

            $this->addFlash('info', "The advert '{$advert->getTitle()}' has been published and released from the archives.");
        }

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => $this->request->query->get('entity'),
        ]);
    }

    public function exportAdvertAction(): StreamedResponse
    {
        $sortDirection = $this->request->query->get('sortDirection');
        if (empty($sortDirection) || !\in_array(mb_strtoupper($sortDirection), ['ASC', 'DESC'], true)) {
            $sortDirection = 'DESC';
        }

        $queryBuilder = $this->createListQueryBuilder(
            $this->entity['class'],
            $sortDirection,
            $this->request->query->get('sortField'),
            $this->entity['list']['dql_filter']
        );

        $data = $queryBuilder->getQuery()->getResult();
        $response = new StreamedResponse();

        $response->setCallback(function () use ($data) {
            $normalizer = new ObjectNormalizer();
            $normalizer->setCircularReferenceHandler(function ($object) {
                return $object->getTitle();
            });

            // Format entity Date export
            $callback = function ($dateTime) {
                return $dateTime instanceof \DateTime
                    ? $dateTime->format(\DateTime::ISO8601)
                    : null;
            };
            $normalizer->setCallbacks(['date' => $callback, 'updatedAt' => $callback]);

            $serializer = new Serializer([$normalizer, new ArrayDenormalizer()], [new CsvEncoder()]);

            // Options supplémentaires pour l'encodeur (optionnel)
            $context = [
                'csv_delimiter' => ';',
                'csv_enclosure' => '"',
                'csv_escape_char' => '\\',
                'csv_key_separator' => '.',
            ];

            $csvContent = $serializer->serialize($data, 'csv', $context);

            return file_put_contents('php://output', $csvContent);
        });

        $filename = $this->entity['label'].'_export'.date('d-m-Y').'.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    public function exportAdvertArchiveAction(): StreamedResponse
    {
        return $this->exportAdvertAction();
    }

    public function exportAction(): void
    {
        throw new \RuntimeException('Action for exporting this entity not defined');
    }
}
