<?php

namespace PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PlatformBundle\Entity\{Advert, AdvertSkill, Application, Image};
use Symfony\Component\Yaml\Yaml;

class LoadAdvert implements FixtureInterface
{
    // In the load method argument, the $manager object is the EntityManager
    public function load(ObjectManager $em): void
    {
        // List of category names to add
        $list = Yaml::parseFile(__DIR__ . '/LoadAvdertWithApplications.yml');
        $listSkills = $em->getRepository('PlatformBundle:Skill')->findAll();

        foreach ($list as ['title' => $title, 'author' => $author, 'content' => $content, 'published' => $published, 'archived' => $archived, 'image' => $imageUrl, 'categories' => $categories, 'application' => $application]) {
            $advert = new Advert();
            $advert
                ->setTitle($title)
                ->setAuthor($author)
                ->setContent($content)
                ->setPublished($published)
                ->setArchived($archived);
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
