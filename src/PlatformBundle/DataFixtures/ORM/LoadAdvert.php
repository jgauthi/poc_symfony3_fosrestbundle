<?php

namespace PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PlatformBundle\Entity\{Advert, AdvertSkill, Application, Image};

class LoadAdvert implements FixtureInterface
{
    // In the load method argument, the $manager object is the EntityManager
    public function load(ObjectManager $em): void
    {
        // List of category names to add
        $liste = [
            [
                'title' => 'Recherche développeur Symfony',
                'author' => 'Eglantine',
                'content' => 'Nous recherchons un **développeur Symfony** débutant sur Lyon. Blabla…',
                'image' => 'http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg',
                'application' => [
                    ['author' => 'Marine', 'content' => 'J\'ai toutes les qualités requises.', 'city' => 'Paris', 'salaryClaim' => 2500],
                    ['author' => 'Pierre', 'content' => 'Je suis très motivé.', 'city' => 'Angoulême', 'salaryClaim' => 2498],
                ],
                'categories' => ['Développement web', 'Intégration'],
            ],
            [
                'title' => 'Poste de CP en cours',
                'author' => 'Delilah',
                'content' => 'Lorem ipsou',
                'image' => null,
                'application' => [
                    ['author' => 'Corvo', 'content' => 'Disponible.', 'city' => 'Dunwall', 'salaryClaim' => 3000],
                    ['author' => 'Emily', 'content' => 'En attente de réponse.', 'city' => 'Dunwall', 'salaryClaim' => 4000],
                ],
                'categories' => null,
            ],
            [
                'title' => 'Développement d\'une Super IA, recherche ingénieur',
                'author' => 'Cave Johnson',
                'content' => 'Recherche ingénieur en _intelligence artificiel_',
                'image' => 'http://localhost/dev/asset/img/specimen/animaux.jpg',
                'application' => null,
                'categories' => ['Réseau'],
            ],
        ];
        $listSkills = $em->getRepository('PlatformBundle:Skill')->findAll();

        foreach ($liste as ['title' => $title, 'author' => $author, 'content' => $content, 'image' => $imageUrl, 'categories' => $categories, 'application' => $application]) {
            $advert = new Advert();
            $advert
                ->setTitle($title)
                ->setAuthor($author)
                ->setContent($content);
            // You can't set the date or the publication because these attributes are defined automatically in the constructor

            // Create image entity
            if (!empty($imageUrl)) {
                $image = new Image();
                $image->setUrl($imageUrl);
                $image->setAlt(basename($imageUrl));
                $advert->setImage($image);
            }

            // Add categories
            $catRepo = $em->getRepository('PlatformBundle:Category');
            if (!empty($categories)) {
                foreach ($categories as $advert_cat) {
                    $category = $catRepo->findOneBy(['name' => $advert_cat]);
                    if (empty($category)) {
                        continue;
                    }

                    $advert->addCategory($category);
                }
            }

            $em->persist($advert);

            if (!empty($application)) {
                foreach ($application as $candidate) {
                    $application = new Application();
                    $application
                    ->setAuthor($candidate['author'])
                    ->setContent($candidate['content'])
                    ->setCity($candidate['city'])
                    ->setSalaryClaim($candidate['salaryClaim'])
                    ->setAdvert($advert);

                    $em->persist($application);
                }
            }

            // Association of skills with the announcement
            foreach (array_rand($listSkills, rand(2, 4)) as $key) {
                $advertSkill = new AdvertSkill();
                $advertSkill->setAdvert($advert)->setSkill($listSkills[$key])->setLevel('Expert');
                $em->persist($advertSkill);
            }
        }

        $em->flush();
    }
}
