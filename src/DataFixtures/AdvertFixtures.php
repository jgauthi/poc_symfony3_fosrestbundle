<?php

namespace App\DataFixtures;

use App\Entity\{AdvertSkill, Application};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

class AdvertFixtures extends Fixture
{
    // In the load method argument, the $manager object is the EntityManager
    public function load(ObjectManager $manager): void
    {
        $aliceFolder = __DIR__.'/Alice';

        Fixtures::load([
                $aliceFolder.'/Skill.yaml',
                $aliceFolder.'/Category.yaml',
                $aliceFolder.'/User.yaml',
                $aliceFolder.'/Advert.yaml',
            ],
            $manager,
            ['providers' => [$this]]
        );
    }


    public function randomCity(): string
    {
        $random = array_rand(Application::CITY_AVAILABLE);
        return Application::CITY_AVAILABLE[$random];
    }

    public function randomAdvertSkill(): string
    {
        $key = array_rand(AdvertSkill::LEVEL_AVAILABLE);
        return AdvertSkill::LEVEL_AVAILABLE[$key];
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
