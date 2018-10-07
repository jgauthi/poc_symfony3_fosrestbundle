<?php
namespace MyUserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MyUserBundle\Entity\User;

class LoadUser implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $listNames = array
        (
            'admin'         =>  array('role' => array('ROLE_ADMIN')),
            'auteur'        =>  array('role' => array('ROLE_AUTEUR')),
            'some_api_user' =>  array('role' => array('ROLE_AUTEUR', 'ROLE_API_ACCESS')),
            'user'          =>  array('role' => array('ROLE_USER')),
        );
        $pass = 'local';

        foreach($listNames as $name => $info)
        {
            $user = new User();
            $user
                ->setUsername($name)
                ->setEmail($name.'@symfony.local')
                ->setPlainPassword($pass)
                ->setRoles($info['role'])
                ->setEnabled(true);

            $manager->persist($user);
        }

        $manager->flush();
    }
}