<?php

namespace App\Controller;

use App\Entity\{Advert, AdvertSkill, Application};
use App\Event\{MessagePostEvent, PlatformEvents};
use App\Form\{AdvertEditType, AdvertType};
use App\Repository\{AdvertRepository, ApplicationRepository};
use App\Service\MarkdownTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\{ParamConverter, Security};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AdvertController
 * @package App\Controller
 * @Route("/{_locale}/platform", name="platform_", requirements={"_locate": "en|fr"})
 */
class AdvertController extends AbstractController
{
    /**
     * @Route("/{page}", methods={"GET"}, name="home", requirements={"page"="\d+"}, defaults={"page": 1})
     * @param string|null $page
     * @param AdvertRepository $advertRepository
     * @param ApplicationRepository $applicationRepository
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function index(?string $page, AdvertRepository $advertRepository, ApplicationRepository $applicationRepository, TranslatorInterface $translator): Response
    {
        if (empty($page) || $page < 1) {
            $page = 1;
        }

        // List advert with paginator
        $nbPerPage = 3;
        $query = $advertRepository->queryRecentActive();
        $query->setFirstResult(($page - 1) * $nbPerPage) // On définit l'annonce à partir de laquelle commencer la liste
            ->setMaxResults($nbPerPage); // Ainsi que le nombre d'annonce à afficher sur une page

        $listAdverts = new Paginator($query, true);

        $nbPages = ceil(count($listAdverts) / $nbPerPage);
        if ($page > $nbPages) {
            throw $this->createNotFoundException($translator->trans("La page %page% n''existe pas.", ['%page%', $page]));
        }

        $listApp = $applicationRepository->getApplicationsWithAdvert(2);

        return $this->render('advert/index.html.twig', [
            'listAdverts' => $listAdverts,
            'listApplication' => $listApp,
            'nbPages' => $nbPages,
            'page' => $page,
        ]);
    }

    /**
     * Left Menu display on Front Office
     * @param AdvertRepository $advertRepository
     * @return Response
     */
    public function menu(AdvertRepository $advertRepository): Response
    {
        $listAdverts = $advertRepository->findBy(
            [],                    // Pas de critère
            ['date' => 'desc'],    // Trie par date récente
            3,                // Nombre d'annonces
            0                // A partir du 1er
        );

        return $this->render('advert/menu.html.twig', [
            'listAdverts' => $listAdverts,
        ]);
    }

    /**
     * @Route("/advert/{id}", methods={"GET"}, name="view")
     * @param Advert $advert
     * @param Request $request
     * @return Response
     */
    public function view(Advert $advert, Request $request): Response
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
            ->getRepository(Application::class)
            ->findBy(['advert' => $advert]);

        // List of skills require for the advert
        $listSkills = $em
            ->getRepository(AdvertSkill::class)
            ->findBy(['advert' => $advert]);

        return $this->render('advert/view.html.twig', [
            'advert' => $advert,
            'tag' => $tag,
            'userId' => $userId,
            'listApplications' => $listApplications,
            'listAdvertSkills' => $listSkills,
        ]);
    }

    /**
     * @Route("/list", methods={"GET"}, name="list")
     * @param AdvertRepository $advertRepository
     * @param MarkdownTransformer $markdownParser
     * @return Response
     */
    public function list(AdvertRepository $advertRepository, MarkdownTransformer $markdownParser): Response
    {
        $articles = ['list_ids' => [], 'listsAdvert' => []];

        $listsAdvert = $advertRepository->getAdvertWithApplications();
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

        return $this->json($articles);
    }

    /**
     * @Route("/add", methods={"GET", "POST"}, name="add")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     */
    public function add(Request $request, TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher): Response
    {
        // Check, alternative to the @Security annotation
        $this->denyAccessUnlessGranted('ROLE_AUTEUR', 'Advert', $translator->trans('advert.admin.author_require'));
        // if (!$this->isGranted('ROLE_AUTEUR', 'Advert')) {
        //    throw new AccessDeniedException($translator->trans('advert.admin.author_require'));
        // }

        // Construction of the form
        $advert = new Advert();
        $advert->setTitle(sprintf('My Advert (%d)', date('Y')));
        $advert->setAuthor('John Doe');

        $form = $this->createForm(AdvertType::class, $advert);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Event bigbrother, check message before save
            $event = new MessagePostEvent($advert->getContent(), $this->getUser());
            $eventDispatcher->dispatch(PlatformEvents::POST_MESSAGE, $event); // We trigger the event

            // We recover what has been modified by the listeners, here the message
            $advert->setContent($event->getMessage());

            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            // Here, we will take care of the creation and management of the form
            $id = $advert->getId();
            $this->addFlash('success', $translator->trans('advert.admin.save_confirm', ['%id%' => $id]));

            // Then we redirect to the advert view
            return $this->redirectToRoute('platform_view', ['id' => $id]);
        }

        // If we are not in POST, then display the form
        return $this->render('advert/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{advert_id}", methods={"GET", "POST"}, name="delete", requirements={"advert_id"="\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("advert", options={"mapping": {"advert_id": "id"}})
     * @param Request $request
     * @param Advert $advert
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Request $request, Advert $advert, TranslatorInterface $translator): Response
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

            $this->addFlash('info', $translator->trans('advert.confirm_delete_true'));

            return $this->redirectToRoute('platform_home');
        }

        return $this->render('advert/delete.html.twig', [
            'advert' => $advert,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", methods={"GET", "POST"}, name="edit", requirements={"id"="\d+"})
     * @Security("has_role('ROLE_AUTEUR')")
     * @param Advert $advert
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function edit(Advert $advert, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(AdvertEditType::class, $advert);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();
                $this->addFlash('info', $translator->trans('advert.admin.edit_confirm_ok', ['%id%' => $advert->getId()]));

                return $this->redirectToRoute('platform_view', ['id' => $advert->getId()]);
            }
        }

        return $this->render('advert/edit.html.twig', ['advert' => $advert, 'form' => $form->createView()]);
    }

    /**
     * @Route("/translation", methods={"GET"}, name="translation")
     * @param $name
     * @return Response
     * exampleUrl: http://localhost:8000/fr/platform/translation/Alice
     */
    public function translation($name): Response
    {
        return $this->render('advert/translation.html.twig', [
            'name' => $name,
        ]);
    }

    /**
     * @Route("/customparamconverter/{json}", methods={"GET"}, name="paramconverter")
     * @param $json
     * @return Response
     * exampleUrl: http://localhost:8000/fr/platform/customparamconverter/{"a":1,"b":2,"c":3}
     */
    public function ParamConverter($json): Response
    {
        return new Response(var_export($json, true));
    }
}
