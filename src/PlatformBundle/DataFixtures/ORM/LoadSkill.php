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
        $objects = Fixtures::load(__DIR__ . '/LoadSkill.yml', $manager);

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
}

?>