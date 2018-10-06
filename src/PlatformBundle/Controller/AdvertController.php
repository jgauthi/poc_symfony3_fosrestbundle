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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class AdvertController extends Controller
{
	//  http://localhost/mindsymfony/web/app_dev.php/fr/platform/
	public function indexAction($page)
	{
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

        return $this->render('@Platform/Advert/index.html.twig', array(
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

        return $this->render('@Platform/Advert/menu.html.twig', array
        (
            'listAdverts' => $listAdverts,
        ));
    }

	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/404
    // http://localhost/mindsymfony/web/fr/platform/404
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/5
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/5?tag=developer
	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/13 ou 14 ou 15
	public function viewAction(Advert $advert, Request $request)
	{
		// Vous avez accès à la requête HTTP via $request (ne pas oublier le use)
		// --> Avec cette façon d'accéder aux paramètres, vous n'avez pas besoin de tester leur existence.
		$tag = $request->query->get('tag');
		if(preg_match('#^(dev|debug)#', $tag))
			dump($request, $advert);

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

	// http://localhost/mindsymfony/web/app_dev.php/fr/platform/list
	public function listAction()
	{
		$articles = ['list_ids' => [], 'listsAdvert' => []];
        $markdownParser = $this->get('platform.service.markdown_transformer');

        $listsAdvert = $this->getDoctrine()->getManager()->getRepository('PlatformBundle:Advert')->getAdvertWithApplications();
        foreach($listsAdvert as $advert)
        {
            $id = $advert->getId();

            $articles['list_ids'][] = $id;
            $articles['listsAdvert'][$id] = [
                'author'            =>  $advert->getAuthor(),
                'title'             =>  $advert->getTitle(),
                'content'           =>  $markdownParser->parse( $advert->getContent() ),
                'nbApplications'    =>  $advert->getNbApplications(),
                'published'         =>  $advert->getPublished(),
            ];
        }

        // dump($articles); return new Response(); // Debug
		if(class_exists('Symfony\Component\HttpFoundation\JsonResponse'))
			return new JsonResponse($articles);

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
            $this->addFlash('notice', $translator->trans('advert.admin.save_confirm', array('%id%' => $id)));

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

            $em = $this->get('doctrine.orm.entity_manager');
            $em->remove($advert);
            $em->flush();

            $translator = $this->get('translator');
            $request->getSession()->getFlashBag()->add('info', $translator->trans('advert.confirm_delete_true'));

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
        // Affichage du formulaire
        $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);

        // Même mécanisme que pour l'ajout
        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);
            if($form->isValid())
            {
				$em = $this->getDoctrine()->getManager();
                $em->flush();

				$translator = $this->get('translator');
                $request->getSession()->getFlashBag()->add('notice', $translator->trans('advert.admin.edit_confirm_ok', array('%id%' => $advert->getId())));

                return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
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
