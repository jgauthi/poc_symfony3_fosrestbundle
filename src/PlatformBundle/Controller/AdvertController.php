<?php

namespace PlatformBundle\Controller;

use PlatformBundle\Entity\Advert;
use PlatformBundle\Event\PlatformEvents;
use PlatformBundle\Event\MessagePostEvent;
use PlatformBundle\Form\AdvertEditType;
use PlatformBundle\Form\AdvertType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


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

	//  http://localhost/mindsymfony/web/app_dev.php/fr/platform/
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
        {
            $translator = $this->get('translator');
            throw $this->createNotFoundException($translator->trans("La page %page% n''existe pas.", array('%page%', $page)));
        }

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
        $currentUser = $this->getUser();

        // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
        return $this->render('@Platform/Advert/menu.html.twig', array
        (
            'listAdverts' => $listAdverts,
        //  'currentUser' => $currentUser, // Non nécessaire, l'user courant est accessible via {{ app.user }}
        ));
    }

	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/404
    // http://localhost/mindsymfony/web/fr/platform/404
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/5
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/5?tag=developer
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/13 ou 14 ou 15
	public function viewAction(Advert $advert, Request $request)
	{
	    $id = $advert->getId();

		// L'annonce n'existe pas (ne pas oublier le use Symfony\Component\HttpFoundation\Response)
		$translator = $this->get('translator');
        if($id == 404)
		{
			$response = new Response();
			$response->setContent($translator->trans('advert.no_exist', array('%id%' => $id)));
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
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/2012/symfony.xml
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/2014/webmaster
	public function viewSlugAction($slug, $year, $_format)
	{
		// Le paramètre {_format}
		// Lorsqu'il est utilisé, alors un header avec le Content-type correspondant est ajouté à la réponse retournée.
		// Exemple : vous appelez/platform/2014/webmaster.xml et le Kernel sait que la réponse retournée par le contrôleur est du XML, grâce au paramètre "_format" contenu dans la route. Ainsi, avant d'envoyer la réponse à notre navigateur, le header Content-type: application/xml sera ajouté.

		return new Response(
			"On pourrait afficher l'annonce correspondant au slug '{$slug}', créée en {$year} et au format {$_format}."
		);
	}

	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/list
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

    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/add
	public function addAction(Request $request)
    {
        $translator = $this->get('translator');

		// Check, alternative à l'annotation @Security
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_AUTEUR'))
            throw new AccessDeniedException($translator->trans('advert.admin.author_require'));

        // Construction du formulaire
		$advert = new Advert();
		$advert->setTitle(sprintf('Mon annonce (%d)', date('Y')));
		$advert->setAuthor('John Doe');

		$form = $this->get('form.factory')->create(AdvertType::class, $advert);
		// alternative depuis le controlleur: $form = $this->createForm(AdvertType::class, $advert)

		if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
		{
            // Evèvenement bigbrother, check message before save
		    $event = new MessagePostEvent($advert->getContent(), $this->getUser());
            $this->get('event_dispatcher')->dispatch(PlatformEvents::POST_MESSAGE, $event); // On déclenche l'évènement

            // On récupère ce qui a été modifié par le ou les listeners, ici le message
            $advert->setContent($event->getMessage());

            // Sauvegarde
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            // Ici, on s'occupera de la création et de la gestion du formulaire
            $id = $advert->getId();
            $request->getSession()->getFlashBag()->add('notice', $translator->trans('advert.admin.save_confirm', array('%id%' => $id)));

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_platform_view', array('id' => $id));
		}

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('@Platform/Advert/add.html.twig', array
		(
			'form'	=>	$form->createView(),
		));
	}

    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/edit/5
    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("advert", options={"mapping": {"advert_id": "id"}})
     */
    public function deleteAction(Request $request, Advert $advert)
    {
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

            $translator = $this->get('translator');
            $request->getSession()->getFlashBah()->add('info', $translator->trans('advert.confirm_delete_true'));

            return $this->redirectToRoute('oc_platform_home');
        }

        return $this->render('@Platform/Advert/delete.html.twig', array
        (
            'advert'    =>  $advert,
            'form'      =>  $form->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_AUTEUR')")
     */
    public function editAction(Advert $advert, Request $request)
    {
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

                $request->getSession()->getFlashBag()->add('notice', $translator->trans('advert.admin.edit_confirm_ok', array('%id%' => $id)));
                return $this->redirectToRoute('oc_platform_view', array('id' => $id));
            }
        }

        return $this->render('@Platform/Advert/edit.html.twig', array('advert' => $advert, 'form' => $form->createView()));
    }

    // http://localhost/mindsymfony/web/app_dev.php/fr/traduction/Alice
    public function translationAction($name)
    {
        return $this->render('@Platform/Advert/translation.html.twig', array
        (
            'name'  =>  $name,
        ));
    }

    /**
     * @param $json
     * @return Response
     * @ParamConverter("json")
     */
    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/customparamconverter/{"a":1,"b":2,"c":3}
    public function ParamConverterAction($json)
    {
        return new Response(var_export($json, true));
    }
}
