<?php

namespace MyRestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
	/**
	 * @Route("/hello", name="hello_world")
	 * @Method({"GET"})
	 * example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/hello
	 */
	public function getHelloAction(Request $request)
	{
		return new JsonResponse([
			array("Tour Eiffel", "5 Avenue Anatole France, 75007 Paris"),
			array("Mont-Saint-Michel", "50170 Le Mont-Saint-Michel"),
			array("Château de Versailles", "Place d'Armes, 78000 Versailles"),
		]);
	}
}
