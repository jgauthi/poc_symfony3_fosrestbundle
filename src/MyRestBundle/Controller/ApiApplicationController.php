<?php

namespace MyRestBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use MyRestBundle\Form\ApplicationType;
use PlatformBundle\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiApplicationController extends Controller
{
	/**
     * Get Last applications with advert
	 * @Rest\View(serializerGroups={"application"})
     * @Rest\Get("/applications")
     * @example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/applications
     */
	public function getApplicationsAction(Request $request)
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
    */
    public function getApplicationAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $advert = $em->getRepository('PlatformBundle:Advert')
            ->find($request->get('id'));

        if(empty($advert))
            return View::create(['message' => 'Advert not found'], Response::HTTP_NOT_FOUND);

        $application = $em->getRepository("PlatformBundle:Application")
            ->findBy(['advert' => $advert]);

        return $application;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"application"})
     * @Rest\Post("/advert/{id}/application")
    */
    public function postApplicationAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $advert = $em->getRepository('PlatformBundle:Advert')
            ->find($request->get('id'));

        if(empty($advert))
            return View::create(['message' => 'Advert not found'], Response::HTTP_NOT_FOUND);

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
