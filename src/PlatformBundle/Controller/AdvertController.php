<?php

namespace PlatformBundle\Controller;

use PlatformBundle\Entity\Advert;
use PlatformBundle\Event\{MessagePostEvent, PlatformEvents};
use PlatformBundle\Form\{AdvertEditType, AdvertType};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\{ParamConverter, Security};
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdvertController extends Controller
{
    //  http://localhost/mindsymfony/web/app_dev.php/fr/platform/
    public function indexAction(?int $page): Response
    {
        if (empty($page) || $page < 1) {
            $page = 1;
        }

        $nbPerPage = 3;
        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em->getRepository('PlatformBundle:Advert')->getAdverts($page, $nbPerPage);
        $nbPages = ceil(count($listAdverts) / $nbPerPage);
        if ($page > $nbPages) {
            $translator = $this->get('translator');
            throw $this->createNotFoundException($translator->trans("La page %page% n''existe pas.", ['%page%', $page]));
        }

        $listApp = $em->getRepository('PlatformBundle:Application')->getApplicationsWithAdvert(2);

        return $this->render('@Platform/Advert/index.html.twig', [
            'listAdverts' => $listAdverts,
            'listApplication' => $listApp,
            'nbPages' => $nbPages,
            'page' => $page,
        ]);
    }

    public function menuAction(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $limit = 3;
        $listAdverts = $em->getRepository('PlatformBundle:Advert')->findBy(
            [],                    // Pas de critère
            ['date' => 'desc'],         // Trie par date récente
            $limit,                     // Nombre d'annonces
            0                           // A partir du 1er
        );

        return $this->render('@Platform/Advert/menu.html.twig', [
            'listAdverts' => $listAdverts,
        ]);
    }

    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/404
    // http://localhost/mindsymfony/web/fr/platform/404
    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/5
    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/5?tag=developer
    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/advert/13 ou 14 ou 15
    public function viewAction(Advert $advert, Request $request): Response
    {
        $tag = $request->query->get('tag');
        if (preg_match('#^(dev|debug)#', $tag)) {
            dump($request, $advert);
        }

        // Current user
        $session = $request->getSession();
        $session->set('user_id', 91);
        $userId = $session->get('user_id');

        // Applications list
        $em = $this->getDoctrine()->getManager();
        $listApplications = $em
            ->getRepository('PlatformBundle:Application')
            ->findBy(['advert' => $advert]);

        // List of skills require for the advert
        $listSkills = $em
            ->getRepository('PlatformBundle:AdvertSkill')
            ->findBy(['advert' => $advert]);

        return $this->render('@Platform/Advert/view.html.twig', [
            'advert' => $advert,
            'tag' => $tag,
            'userId' => $userId,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listSkills,
        ]);
    }

    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/list
    public function listAction(): Response
    {
        $articles = ['list_ids' => [], 'listsAdvert' => []];
        $markdownParser = $this->get('platform.service.markdown_transformer');

        $listsAdvert = $this->getDoctrine()->getManager()->getRepository('PlatformBundle:Advert')->getAdvertWithApplications();
        foreach ($listsAdvert as $advert) {
            $id = $advert->getId();

            $articles['list_ids'][] = $id;
            $articles['listsAdvert'][$id] = [
                'author' => $advert->getAuthor(),
                'title' => $advert->getTitle(),
                'content' => $markdownParser->parse($advert->getContent()),
                'nbApplications' => $advert->getNbApplications(),
                'published' => $advert->getPublished(),
            ];
        }

        // dump($articles); return new Response(); // Debug
        if (class_exists('Symfony\Component\HttpFoundation\JsonResponse')) {
            return new JsonResponse($articles);
        }

        $response = new Response(json_encode($articles));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/add
    public function addAction(Request $request): Response
    {
        $translator = $this->get('translator');

        // Check, alternative to the @Security annotation
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_AUTEUR')) {
            throw new AccessDeniedException($translator->trans('advert.admin.author_require'));
        }
        // Construction of the form
        $advert = new Advert();
        $advert->setTitle(sprintf('My Advert (%d)', date('Y')));
        $advert->setAuthor('John Doe');

        $form = $this->get('form.factory')->create(AdvertType::class, $advert);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Event bigbrother, check message before save
            $event = new MessagePostEvent($advert->getContent(), $this->getUser());
            $this->get('event_dispatcher')->dispatch(PlatformEvents::POST_MESSAGE, $event); // We trigger the event

            // We recover what has been modified by the listeners, here the message
            $advert->setContent($event->getMessage());

            // Sauvegarde
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            // Here, we will take care of the creation and management of the form
            $id = $advert->getId();
            $this->addFlash('notice', $translator->trans('advert.admin.save_confirm', ['%id%' => $id]));

            // Then we redirect to the advert view
            return $this->redirectToRoute('oc_platform_view', ['id' => $id]);
        }

        // If we are not in POST, then display the form
        return $this->render('@Platform/Advert/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/edit/5

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("advert", options={"mapping": {"advert_id": "id"}})
     */
    public function deleteAction(Request $request, Advert $advert): Response
    {
        // Create an empty form, which will contain only the CSRF field
        // This will protect ad deletion against this flaw
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Deletion of linked categories
            foreach ($advert->getCategories() as $category) {
                $advert->removeCategory($category);
            }

            $em = $this->get('doctrine.orm.entity_manager');
            $em->remove($advert);
            $em->flush();

            $translator = $this->get('translator');
            $request->getSession()->getFlashBag()->add('info', $translator->trans('advert.confirm_delete_true'));

            return $this->redirectToRoute('oc_platform_home');
        }

        return $this->render('@Platform/Advert/delete.html.twig', [
            'advert' => $advert,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_AUTEUR')")
     */
    public function editAction(Advert $advert, Request $request): Response
    {
        // Affichage du formulaire
        $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);

        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $translator = $this->get('translator');
                $request->getSession()->getFlashBag()->add('notice', $translator->trans('advert.admin.edit_confirm_ok', ['%id%' => $advert->getId()]));

                return $this->redirectToRoute('oc_platform_view', ['id' => $advert->getId()]);
            }
        }

        return $this->render('@Platform/Advert/edit.html.twig', ['advert' => $advert, 'form' => $form->createView()]);
    }

    // http://localhost/mindsymfony/web/app_dev.php/fr/traduction/Alice
    public function translationAction($name): Response
    {
        return $this->render('@Platform/Advert/translation.html.twig', [
            'name' => $name,
        ]);
    }

    /**
     * @param $json
     *
     * @return Response
     * @ParamConverter("json")
     */
    // http://localhost/mindsymfony/web/app_dev.php/fr/platform/customparamconverter/{"a":1,"b":2,"c":3}
    public function ParamConverterAction($json): Response
    {
        return new Response(var_export($json, true));
    }
}
