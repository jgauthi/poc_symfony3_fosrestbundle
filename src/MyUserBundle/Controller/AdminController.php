<?php
namespace MyUserBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use FOS\UserBundle\Model\UserInterface;

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
}
