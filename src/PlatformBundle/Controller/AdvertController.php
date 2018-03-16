<?php

namespace PlatformBundle\Controller;

use PlatformBundle\Entity\Advert;
use PlatformBundle\Entity\AdvertSkill;
use PlatformBundle\Entity\Application;
use PlatformBundle\Entity\Image;
use PlatformBundle\Form\AdvertEditType;
use PlatformBundle\Form\AdvertType;
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

	public function indexAction($page)
	{
		// On veut récupérer l'url de l'annonce #5
		// Arguments generate: nom de la route, paramètres
		$url = $this->get('router')->generate('oc_platform_view', array('id' => 5));

		// Comme notre contrôleur hérite du contrôleur de base de Symfony, nous avons également accès à une méthode raccourcie pour générer des routes. Voici une alternative strictement équivalente :
        $url2 = $this->generateUrl('oc_platform_view', array('id' => 7), UrlGeneratorInterface::ABSOLUTE_URL);

		$random_msg = "L'url de l'annonce 5 est: {$url}, annonce 7: {$url2}";

        // Notre liste d'annonce
        $repo = $this->getDoctrine()->getManager()->getRepository('PlatformBundle:Category');

        /*
        $categories = $repo->findByName(array('Développement web', 'Développement mobile'));
        dump($categories);

        $listAdverts = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlatformBundle:Advert')
            ->getAdvertWithCategories($categories);
        dump($listAdverts);
        */

        if(empty($page) || $page < 1)
            $page = 1;

        $nbPerPage = 3;
        $listAdverts = $this->getDoctrine()
            ->getManager()
            ->getRepository('PlatformBundle:Advert')
            ->getAdverts($page, $nbPerPage);

        $nbPages = ceil(count($listAdverts) / $nbPerPage);
        if($page > $nbPages)
            throw $this->createNotFoundException("La page {$page} n'existe pas.");

        $listApp = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlatformBundle:Application')
            ->getApplicationsWithAdvert(2);
        dump($listApp);

        // On vérifie si cette IP a déjà posté une candidature il y a moins de 15 secondes
        $lastAdvert = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('PlatformBundle:Advert')
            ->getLastAdverts(1);

        if(!empty($lastAdvert[0]))
        {
            $currentDate = new \DateTime();
            $date = $lastAdvert[0]->getDate();

            $diff = $date->diff($currentDate);
            dump($diff->d);
        }


        return $this->render('@Platform/Advert/index.html.twig', array(
            'random_msg'        => $random_msg,
            'listAdverts'       => $listAdverts,
            'listApplication'   => $listApp,
            'nbPages'           => $nbPages,
            'page'              => $page,
        ));
	}

    public function menuAction()
    {
        $em = $this->getDoctrine()->getManager();
        $limit = 3;
        $listAdverts = $em->getRepository('PlatformBundle:Advert')->findBy
        (
            array(),                    // Pas de critère
            array('date' => 'desc'),    // Trie par date récente
            $limit,                     // Nombre d'annonces
            0                           // A partir du 1er
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
        if(null === $advert)
            throw new NotFoundHttpException("L'annonce d'id #{$id} n'existe pas.");

        // Liste des candidatures
		$em = $this->getDoctrine()->getManager();
        $listApplications = $em
            ->getRepository('PlatformBundle:Application')
            ->findBy(array('advert' => $advert));

        // Liste des compétences require pour l'annonce
		$listSkills = $em
			->getRepository('PlatformBundle:AdvertSkill')
			->findBy(array('advert' => $advert));

        return $this->render('@Platform/Advert/view.html.twig', array(
            'advert'            =>  $advert,
            'tag' 		        =>	$tag,
            'userId'	        =>	$userId,
            'listApplications'  =>  $listApplications,
			'listAdvertSkills'	=>	$listSkills,
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
		$articles = array
        (
            'list_ids'      => array(5, 13, 14, 15, 404),
            'listsAdvert'   => null,
        );

		$listsAdvert = $this->getDoctrine()->getManager()->getRepository('PlatformBundle:Advert')->getAdvertWithApplications();
        $articles['listsAdvert'] = $listsAdvert;

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
		/*
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

        // Création de l'entité Image
        $image = new Image();
        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        $image->setAlt('Job de rêve');

        // On lie l'image à l'annonce
        $advert->setImage($image);

        // Candidature 1
        $application1 = new Application();
        $application1->setAuthor('Marine')->setContent('J\'ai toutes les qualités requises.')->setAdvert($advert);

        // Candidature 2
        $application2 = new Application();
        $application2->setAuthor('Pierre')->setContent('Je suis très motivé.')->setAdvert($advert);

		// On récupère l'EntityManager
		$em = $this->getDoctrine()->getManager();

		// Association de compétences à l'annonce
		$listSkills = $em->getRepository('PlatformBundle:Skill')->findAll();
		foreach($listSkills as $skill)
		{
			$advertSkill = new AdvertSkill();
			$advertSkill->setAdvert($advert)->setSkill($skill)->setLevel('Expert');
			$em->persist($advertSkill);
		}

        // Étape 1 : On « persiste » l'entité
        $em->persist($advert);
        $em->persist($application1);
        $em->persist($application2);

        // Étape 2 : Flush: Ouvre une transaction et enregistre toutes les entités qui t'ont été données depuis le(s) dernier(s) flush()
        $em->flush();
		*/

        // Construction du formulaire
		$advert = new Advert();
		$advert->setTitle(sprintf('Mon annonce (%d)', date('Y')));
		$advert->setAuthor('John Doe');

		$form = $this->get('form.factory')->create(AdvertType::class, $advert);
		// alternative depuis le controlleur: $form = $this->createForm(AdvertType::class, $advert)

		if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
		{
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            // Ici, on s'occupera de la création et de la gestion du formulaire
            $id = $advert->getId();
            $request->getSession()->getFlashBag()->add('notice', "Annonce #{$id} bien enregistrée");

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_platform_view', array('id' => $id));
		}

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('@Platform/Advert/add.html.twig', array
		(
			'form'	=>	$form->createView(),
		));
	}

    // http://localhost/mindsymfony/web/app_dev.php/platform/edit/5
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('PlatformBundle:Advert')->find($id);
        if(null === $advert)
        	throw new NotFoundHttpException("L'annonce {$id} n'existe pas.");

		// La méthode findAll retourne toutes les catégories de la base de données
        /*$listCategories = $advert->getCategories();
        if($listCategories->isEmpty())
        {
            $listCategories = $em->getRepository('PlatformBundle:Category')->findAll();
            foreach($listCategories as $category)
                $advert->addCategory($category);
        }

		$em->flush();*/

        // Affichage du formulaire
        $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);

        // Même mécanisme que pour l'ajout
        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            if($form->isValid())
            {
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', "Annonce #{$id} bien modifiée.");
                return $this->redirectToRoute('oc_platform_view', array('id' => $id));
            }
        }

        return $this->render('@Platform/Advert/edit.html.twig', array('advert' => $advert, 'form' => $form->createView()));
    }

    public function deleteAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$advert = $em->getRepository('PlatformBundle:Advert')->find($id);

    	if(null === $advert)
    	    throw new NotFoundHttpException("L'annonce d'id {$id} n'existe pas.");

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            // Suppression des categories liés
            foreach($advert->getCategories() as $category)
                $advert->removeCategory($category);

            // Suppression des skills
            //foreach($advert->getS)

            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBah()->add('info', "L'annonce a bien été supprimé.");

            return $this->redirectToRoute('oc_platform_home');
        }

        return $this->render('@Platform/Advert/delete.html.twig', array
        (
            'advert'    =>  $advert,
            'form'      =>  $form->createView(),
        ));
    }

}
