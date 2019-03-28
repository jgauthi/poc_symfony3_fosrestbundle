<?php

namespace App\DataFixtures;

use App\Entity\{Advert, AdvertSkill, Application, Category, Image, Skill};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;
use Symfony\Component\Yaml\Yaml;

class AdvertFixtures extends Fixture
{
    const MAIL_DOMAIN = 'symfony.local';
    const PASSWORD = 'local';

    // In the load method argument, the $manager object is the EntityManager
    public function load(ObjectManager $manager): void
    {
        $aliceFolder = __DIR__.'/Alice';

        Fixtures::load([
                $aliceFolder.'/Skill.yaml',
                $aliceFolder.'/Category.yaml',
                $aliceFolder.'/Advert.yaml',
                $aliceFolder.'/User.yaml',
            ],
            $manager,
            ['providers' => [$this]]
        );
    }


    public function randomCity(): string
    {
        static $city = ['Paris', 'Dunwall', 'Angoulême', 'Nice'];

        $random = array_rand($city);
        return $city[$random];
    }

    public function randomAdvertSkill(): string
    {
        static $names = [
            'Bas', 'Moyen', 'Bon', 'Expert'
        ];

        $key = array_rand($names);
        return $names[$key];
    }

    public function randomSkill(): string
    {
        static $names = [
            'PHP', 'Symfony',
            'Wordpress', 'Typo3',
            'C++', 'Java',
            'Javscript', 'jQuery',
            'Photoshop', 'Fireworks',
            'Blender', 'Shell script',
            'Bloc-note', 'PhpStorm',
            'Ruby', 'Rails',
            'Présentation',
            'Fixture', 'Generation',
        ];

        $key = array_rand($names);

        // Return a uniq random value
        $value = $names[$key];
        unset($names[$key]);

        return $value;
    }
}
