<?php

namespace MyRestBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use MyRestBundle\Form\ApplicationType;
use PlatformBundle\Entity\Advert;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiApplicationController extends Controller
{
	/**
     * Get Last applications with advert
	 * @Rest\View()
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
}
