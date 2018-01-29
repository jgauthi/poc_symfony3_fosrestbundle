<?php

namespace PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{
	public function helloAction()
	{
		$content = $this->get('twig')->render('@Platform/Advert/hello.html.twig', array('nom' => 'John doe'));

		/*
		  Cette syntaxe $this->get('mon_service') depuis les contrôleurs retourne un objet dont le nom est "mon_service" , cet objet permet ensuite d'effectuer quelques actions. Par exemple ici l'objet "twig" permet de récupérer le contenu d'un template grâce à sa méthode render.

		  Ces objets, appelés services, sont une fonctionnalité phare de Symfony, que nous étudions très en détails dans la prochaine partie de ce cours. Je vais vous demander un peu de patience, en attendant vous pouvez les utiliser sans forcément comprendre d'où ils viennent.
		*/

		return new Response($content);
	}
}
