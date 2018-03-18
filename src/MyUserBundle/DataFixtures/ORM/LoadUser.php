<?php
namespace MyUserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MyUserBundle\Entity\User;

class LoadUser implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $listNames = array
        (
            'admin'      =>  array('role' => array('ROLE_ADMIN'), 'salt' => ''),
            'auteur'     =>  array('role' => array('ROLE_AUTEUR'), 'salt' => ''),
            'user'       =>  array('role' => array('ROLE_USER'), 'salt' => ''),
        );
        $pass = 'local';

        foreach($listNames as $name => $info)
        {
            $user = new User();
            $user->setUsername($name)->setPassword($pass);
            $user->setSalt($info['salt']);
            $user->setRoles($info['role']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}