<?php

namespace PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PlatformBundle\Entity\Category;

class LoadCategory implements FixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager): void
    {
        // Liste des noms de catégorie à ajouter
        $names = [
            'Développement web',
            'Développement mobile',
            'Graphisme',
            'Intégration',
            'Réseau',
        ];

        foreach ($names as $name) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
