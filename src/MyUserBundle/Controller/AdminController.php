<?php
namespace MyUserBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
}