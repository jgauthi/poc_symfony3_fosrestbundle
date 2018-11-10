<?php
namespace MyUserBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\{StreamedResponse, RedirectResponse};

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
        $response =  parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        // Display applications/advertSkill from not archived advert
        if(preg_match('#PlatformBundle\\\Entity\\\(Application|AdvertSkill)#i', $entityClass)) {
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

        $advert = $em->getRepository('PlatformBundle:Advert')->find($id);

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
        if (empty($sortDirection) || !in_array(strtoupper($sortDirection), ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }

        $queryBuilder = $this->createListQueryBuilder(
            $this->entity['class'],
            $sortDirection,
            $this->request->query->get('sortField'),
            $this->entity['list']['dql_filter']
        );

        $data = new ArrayCollection($queryBuilder->getQuery()->getResult());
        $response = new StreamedResponse();

        $response->setCallback(function() use ($data) {
            $handle = fopen('php://output', 'w+');
            $csvDelimiter = ';';
            $csvEnclosure = '"';
            $titleDisplayed = false;

            while ($entity = $data->current()) {
                $values = [
                    'id'            => $entity->getId(),
                    'title'         => $entity->getTitle(),
                    'author'        => $entity->getAuthor(),
                    'content'       => $entity->getContent(),
                    'date'          => $entity->getDate()->format('d/m/Y'),
                    'updatedAt'     => $entity->getUpdatedAt(),
                    'published'     => (($entity->getPublished()) ? 'Oui' : 'Non'),
                    'categories'    => null,
                    'applications'  => null,
                ];

                if(!empty($values['updatedAt']))
                    $values['updatedAt'] = $values['updatedAt']->format('d/m/Y');

                $categories = $entity->getCategories()->getValues();
                if(!empty($categories)) {
                    $list = [];
                    foreach($categories as $item) {
                        $list[] = $item->getName();
                    }

                    $values['categories'] = implode(', ', $list);
                }

                $applications = $entity->getApplications()->getValues();
                if(!empty($applications)) {
                    $list = [];
                    foreach($applications as $item) {
                        $list[] = "{$item->getAuthor()} de {$item->getCity()} ({$item->getSalaryClaim()})";
                    }

                    $values['applications'] = implode(', ', $list);
                }

                // Add header
                if(!$titleDisplayed) {
                    fputcsv($handle, array_keys($values), $csvDelimiter, $csvEnclosure);
                    $titleDisplayed = true;
                }

                fputcsv($handle, $values, $csvDelimiter, $csvEnclosure);
                $data->next();
            }
            fclose($handle);
        });

        $filename = $this->entity['label'].'_export'.date('d-m-Y').'.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

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
