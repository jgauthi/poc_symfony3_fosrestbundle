<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

// use App\Entity\Skill;

class SkillFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $objects = Fixtures::load(
            __DIR__.'/LoadSkill.yml',
            $manager,
            ['providers' => [$this]]
        );

        /*
        // Liste des noms de compétences à ajouter
        $names = ['PHP', 'Symfony', 'C++', 'Java', 'Photoshop', 'Blender', 'Bloc-note'];

        foreach($names as $name)
        {
            // On crée la compétence
            $skill = new Skill();
            $skill->setName($name);

            // On la persiste
            $manager->persist($skill);
        }

        // On déclenche l'enregistrement de toutes les catégories
        $manager->flush();
        */
    }

    public function custom_skill(): string
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
