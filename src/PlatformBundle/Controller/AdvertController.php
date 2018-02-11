<?php

namespace PlatformBundle\Controller;

use PlatformBundle\Entity\Advert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
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

		$random_msg = "L'url de l'annonce 5 est: {$url}, annonce 7: {$url2}";

        // Notre liste d'annonce en dur
        $listAdverts = array
        (
            array
            (
                'title'   => 'Recherche développpeur Symfony',
                'id'      => 1,
                'author'  => 'Alexandre',
                'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                'date'    => new \Datetime()),
            array
            (
                'title'   => 'Mission de webmaster',
                'id'      => 2,
                'author'  => 'Hugo',
                'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                'date'    => new \Datetime()),
            array
            (
                'title'   => 'Offre de stage webdesigner',
                'id'      => 3,
                'author'  => 'Mathieu',
                'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                'date'    => new \Datetime())
        );


        return $this->render('@Platform/Advert/index.html.twig', array(
            'random_msg' => $random_msg,
            'listAdverts' => $listAdverts,
        ));
	}

    public function menuAction()
    {
        $listAdverts = array
        (
            array('id' => 2, 'title' => 'Recherche développeur Symfony'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
        return $this->render('@Platform/Advert/menu.html.twig', array
        (
            'listAdverts' => $listAdverts
        ));
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

		// User en cours
		$session = $request->getSession();
		$session->set('user_id', 91);
		$userId = $session->get('user_id');

        // On récupère le repository & l'entité correspondante à l'id $id
        $repository = $this->getDoctrine()->getManager()->getRepository('PlatformBundle:Advert');
        $advert = $repository->find($id);

        // $advert est donc une instance de PlatformBundle\Entity\Advert ou null si l'$id  n'existe pas
        if (null === $advert)
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");

        return $this->render('@Platform/Advert/view.html.twig', array(
            'advert'    =>  $advert,
            'tag' 		=>	$tag,
            'userId'	=>	$userId,
        ));
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

	// http://localhost/mindsymfony/web/app_dev.php/platform/list
	public function listAction()
	{
		$articles = array('list_ids' => array(5, 13, 14, 15, 404));

		if(class_exists('Symfony\Component\HttpFoundation\JsonResponse'))
			return new JsonResponse($articles);

		// Alternative manuel
		$response = new Response(json_encode($articles));
		$response->headers->set('Content-Type', 'application/json');

		return $response;
	}

    // http://localhost/mindsymfony/web/app_dev.php/platform/add
	public function addAction(Request $request)
    {
        $txt = "Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…";

        // On récupère le service
        $antispam = $this->container->get('platform.antispam');
        if($antispam->isSpam($txt))
            throw new \Exception('Votre message a été détecté comme spam !');

        // Création de l'entité
        $advert = new Advert();
        $advert
            ->setTitle('Recherche développeur Symfony.')
            ->setAuthor('Alexandre')
            ->setContent($txt);

        // On peut ne pas définir ni la date ni la publication, car ces attributs sont définis automatiquement dans le constructeur

        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » l'entité
        $em->persist($advert);

        // Étape 2 : Flush: Ouvre une transaction et enregistre toutes les entités qui t'ont été données depuis le(s) dernier(s) flush()
        $em->flush();

        if($request->isMethod('POST'))
        {
            $id = $advert->getId();

            // Ici, on s'occupera de la création et de la gestion du formulaire
            $request->getSession()->getFlashBag()->add('notice', "Annonce #{$id} bien enregistrée");

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_platform_view', array('id' => $id));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('@Platform/Advert/add.html.twig');
	}

    // http://localhost/mindsymfony/web/app_dev.php/platform/edit/5
    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id

        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST'))
        {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
            return $this->redirectToRoute('oc_platform_view', array('id' => 5));
        }

        $advert = array
        (
            'title'   => 'Recherche développpeur Symfony',
            'id'      => $id,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()
        );

        return $this->render('@Platform/Advert/edit.html.twig', array('advert' => $advert));
    }

    public function deleteAction($id)
    {
        // Ici, on récupérera l'annonce correspondant à $id
        // Ici, on gérera la suppression de l'annonce en question
        return $this->render('@Platform/Advert/delete.html.twig');
    }

}
