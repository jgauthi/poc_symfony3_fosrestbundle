<?php

namespace App\Controller\Api;

use App\Entity\Advert;
use App\Entity\Application;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use FOS\RestBundle\{Controller\Annotations as Rest, View\View};
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};

/**
 * Class ApplicationController.
 *
 * @Rest\NamePrefix(value="api_")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Rest\View(serializerGroups={"application", "user-simple"})
     * @Rest\Get("/applications")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Application",
     *     description="Récupère la liste des candidatures",
     *     output= { "class"=Application::class, "collection"=true, "groups"={"application"} },
     *     headers={
     *         { "name"="X-Auth-Token", "required"=false, "description"="Authorization key" },
     *    }
     * )
     * exampleUrl: http://localhost:8000/fr/api/v1/applications
     *
     * @param Request               $request
     * @param ApplicationRepository $applicationRepository
     *
     * @return array
     */
    public function getApplicationsAction(Request $request, ApplicationRepository $applicationRepository): array
    {
        $limit = $request->get('limit', 5);
        if (!is_numeric($limit) || $limit > 10) {
            $limit = 10;
        }

        $applications = $applicationRepository->getApplicationsWithAdvert($limit);

        return $applications;
    }

    /**
     * @Rest\View(serializerGroups={"application", "user-simple"})
     * @Rest\Get("/advert/{id}/application")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Application",
     *     description="Récupère une candiature",
     *     output="App\Entity\Application",
     *     requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirements"="\d+",
     *             "description"="Identifiant de l'annonce"
     *         }
     *     },
     *     headers={
     *         { "name"="X-Auth-Token", "required"=true, "description"="Authorization key" },
     *    }
     * )
     *
     * @param Request $request
     *
     * @return array
     */
    public function getApplicationAction(Request $request): array
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository(Advert::class)->find($request->get('id'));
        if (empty($advert)) {
            throw $this->createNotFoundException('Advert not found');
        }

        $application = $em->getRepository(Application::class)->findBy(['advert' => $advert]);

        return $application;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"application"})
     * @Rest\Post("/advert/{id}/application")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Application",
     *     description="Ajouter une candidature",
     *     input="App\Form\ApplicationType",
     *     statusCodes = {
     *        201 = "Création avec succès",
     *        400 = "Formulaire invalide",
     *        405 = "Candidature déjà existante"
     *    },
     *    responseMap={
     *         201 = {"class"=Application::class, "groups"={"application"}},
     *         400 = {"class"=Application::class, "form_errors"=true, "name" = ""},
     *         405 = {"class"=Application::class, "form_errors"=true, "name" = ""}
     *    },
     *    requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirements"="\d+",
     *             "description"="Identifiant de l'annonce"
     *         }
     *    },
     *    headers={
     *         { "name"="X-Auth-Token", "required"=true, "description"="Authorization key" },
     *    }
     * )
     *
     * @param Request $request
     *
     * @return object
     */
    public function postApplicationAction(Request $request): Object
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Advert $advert */
        $advert = $em->getRepository(Advert::class)->find($request->get('id'));
        if (empty($advert)) {
            throw $this->createNotFoundException('Advert not found');
        }

        // Already subscribe?
        $application = $em->getRepository(Application::class)->findOneBy(['advert' => $advert, 'author' => $this->getUser()]);
        if (!empty($application)) {
            return View::create([
                    'message' => 'The application already exists for current user of this advert.',
                    'current_application' => $application,
                ],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }

        $application = new application();
        $application
            ->setAdvert($advert)
            ->setAuthor($this->getUser());

        $form = $this->createForm(ApplicationType::class, $application, ['csrf_protection' => false]);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $em->persist($application);
        $em->flush();

        return $application;
    }
}
