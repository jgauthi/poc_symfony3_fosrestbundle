<?php

namespace MyRestBundle\Controller;

use FOS\RestBundle\{Controller\Annotations as Rest, View\View};
use MyRestBundle\Form\ApplicationType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use PlatformBundle\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Request, Response};

class ApiApplicationController extends Controller
{
	/**
	 * @Rest\View(serializerGroups={"application"})
     * @Rest\Get("/applications")
     * @example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/applications
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
     */
	public function getApplicationsAction(Request $request): array
    {
        $limit = $request->get('limit', 5);
        if(!is_numeric($limit) || $limit > 10)
            $limit = 10;

        $applications = $this->get('doctrine.orm.entity_manager')
            ->getRepository('PlatformBundle:Application')
            ->getApplicationsWithAdvert($limit);

        return $applications;
    }

    /**
     * @Rest\View(serializerGroups={"application"})
     * @Rest\Get("/advert/{id}/application")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Application",
     *     description="Récupère une candiature",
     *     output="PlatformBundle\Entity\Application",
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
    */
    public function getApplicationAction(Request $request): array
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $advert = $em->getRepository('PlatformBundle:Advert')
            ->find($request->get('id'));

        if(empty($advert))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Advert not found');

        $application = $em->getRepository("PlatformBundle:Application")
            ->findBy(['advert' => $advert]);

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
     *     input="MyRestBundle\Form\ApplicationType",
     *     statusCodes = {
     *        201 = "Création avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         201 = {"class"=Application::class, "groups"={"application"}},
     *         400 = { "class"=Application::class, "form_errors"=true, "name" = ""}
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
    */
    public function postApplicationAction(Request $request): Object
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $advert = $em->getRepository('PlatformBundle:Advert')
            ->find($request->get('id'));

        if(empty($advert))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Advert not found');

        $application = new application();
        $application->setAdvert($advert);

        $form = $this->createForm(ApplicationType::class, $application);
        $form->submit($request->request->all());

        if(!$form->isValid())
            return $form;

        $em->persist($application);
        $em->flush();

        return $application;
    }
}
