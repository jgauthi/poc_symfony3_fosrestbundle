<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    const MAIL_DOMAIN = 'symfony.local';
    const PASSWORD = 'local';
    const USERS = [
        'admin' => ['role' => ['ROLE_ADMIN']],
        'auteur' => ['role' => ['ROLE_AUTEUR']],
        'some_api_user' => ['role' => ['ROLE_AUTEUR', 'ROLE_API_ACCESS']],
        'user' => ['role' => ['ROLE_USER']],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $name => ['role' => $role]) {
            $user = new User();
            $user
                ->setUsername($name)
                ->setEmail($name.'@'.self::MAIL_DOMAIN)
                ->setPlainPassword(self::PASSWORD)
                ->setRoles($role)
                ->setEnabled(true);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
