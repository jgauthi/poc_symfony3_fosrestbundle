<?php

namespace PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdvertController extends Controller
{
	public function helloAction()
	{
		$content = $this->get('twig')->render('@Platform/Advert/hello.html.twig', array
		(
			'advert_id'	=>	5,
			'nom' 		=> 'John doe',
		));

		/*
		  Cette syntaxe $this->get('mon_service') depuis les contrôleurs retourne un objet dont le nom est "mon_service" , cet objet permet ensuite d'effectuer quelques actions. Par exemple ici l'objet "twig" permet de récupérer le contenu d'un template grâce à sa méthode render.

		  Ces objets, appelés services, sont une fonctionnalité phare de Symfony, que nous étudions très en détails dans la prochaine partie de ce cours. Je vais vous demander un peu de patience, en attendant vous pouvez les utiliser sans forcément comprendre d'où ils viennent.
		*/

		return new Response($content);
	}

	public function indexAction()
	{
		// On veut récupérer l'url de l'annonce #5
		// Arguments generate: nom de la route, paramètres
		$url = $this->get('router')->generate('oc_platform_view', array('id' => 5));

		// Comme notre contrôleur hérite du contrôleur de base de Symfony, nous avons également accès à une méthode raccourcie pour générer des routes. Voici une alternative strictement équivalente :
		$url2 = $this->generateUrl('oc_platform_view', array('id' => 7), UrlGeneratorInterface::ABSOLUTE_URL);

		return new Response("L'url de l'annonce 5 est: {$url}, annonce 7: {$url2}");
	}


	// http://localhost/mindsymfony/web/app_dev.php/platform/advert/404
	// http://localhost/mindsymfony/web/app_dev.php/platform/advert/5
	// http://localhost/mindsymfony/web/app_dev.php/platform/advert/5?tag=developer
	// http://localhost/mindsymfony/web/app_dev.php/platform/advert/13 ou 14 ou 15
	public function viewAction($id, Request $request)
	{
		// L'annonce n'existe pas (ne pas oublier le use Symfony\Component\HttpFoundation\Response)
		if($id == 404)
		{
			$response = new Response();
			$response->setContent("L'annonce {$id} n'existe pas.");
			$response->setStatusCode(Response::HTTP_NOT_FOUND);

			return $response;
		}
		// Revenir à la homepage
		elseif($id == 13)
		{
			$url = $this->get('router')->generate('oc_platform_home');
			return new RedirectResponse($url);
		}
		// (alternative) passer par la méthode raccourci (ne nécessite pas le use RedirectResponse)
		elseif($id == 14)
		{
			$url = $this->get('router')->generate('oc_platform_home');
			return $this->redirect($url);
		}
		// (alternative) passer par la méthode raccourci en indiquant la vue (ne nécessite pas le use RedirectResponse)
		elseif($id == 15)
			return $this->redirectToRoute('oc_platform_home');

		// Pour debugguer les redirections: "intercept_redirects" à true dans "app/config/config_dev.yml"


		// Vous avez accès à la requête HTTP via $request (ne pas oublier le use)
		// --> Avec cette façon d'accéder aux paramètres, vous n'avez pas besoin de tester leur existence.
		$tag = $request->query->get('tag');
		if(preg_match('#^(dev|debug)#', $tag))
			dump($request);

		/*
			$_GET 							--> $request->query->get('tag')
			$_POST 							--> $request->request->get('tag')
			$_COOKIE 						--> $request->cookies->get('tag')
			$_SERVER						--> $request->server->get('REQUEST_URI')
			$_SERVER['HTTP_*']				--> $request->headers->get('USER_AGENT')
			$id	(alternative)				--> $request->attributes->get('id')

			$request->isMethod('POST')		--> Vérifier la méthode HTTP en cours
			$request->isXmlHttpRequest()	--> Si Ajax

			Pour en savoir plus:
			http://api.symfony.com/3.0/Symfony/Component/HttpFoundation/Request.html
		*/

		return new Response("Affichage de l'annonce d'id: {$id}, avec le tag: {$tag}");
	}

	// On récupère tous les paramètres en arguments de la méthode
	// http://localhost/mindsymfony/web/app_dev.php/platform/2012/symfony.xml
	// http://localhost/mindsymfony/web/app_dev.php//platform/2014/webmaster
	public function viewSlugAction($slug, $year, $_format)
	{
		// Le paramètre {_format}
		// Lorsqu'il est utilisé, alors un header avec le Content-type correspondant est ajouté à la réponse retournée.
		// Exemple : vous appelez/platform/2014/webmaster.xml et le Kernel sait que la réponse retournée par le contrôleur est du XML, grâce au paramètre "_format" contenu dans la route. Ainsi, avant d'envoyer la réponse à notre navigateur, le header Content-type: application/xml sera ajouté.

		return new Response(
			"On pourrait afficher l'annonce correspondant au slug '{$slug}', créée en {$year} et au format {$_format}."
		);
	}

}
