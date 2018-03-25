<?php
namespace PlatformBundle\DataFixtures\ORM;

use Nelmio\Alice\Fixtures;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
// use PlatformBundle\Entity\Skill;

class LoadSkill implements FixtureInterface
{
	public function load(ObjectManager $manager)
	{
        $objects = Fixtures::load
        (
            __DIR__ . '/LoadSkill.yml',
            $manager,
            array('providers' => array($this))
        );

	    /*
		// Liste des noms de compétences à ajouter
		$names = array('PHP', 'Symfony', 'C++', 'Java', 'Photoshop', 'Blender', 'Bloc-note');

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

	public function custom_skill()
    {
        static $names = array
        (
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
        );

        $key = array_rand($names);

        // Return a uniq random value
        $value = $names[$key];
        unset($names[$key]);

        return $value;
    }
}

?>