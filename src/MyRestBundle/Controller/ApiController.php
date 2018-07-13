<?php

namespace MyRestBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
	/**
	 * @Rest\Get("/adverts")
	 * example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/adverts
	 */
	public function getAdvertsAction()
	{
		$advert_list = $this->get('doctrine.orm.entity_manager')
			->getRepository('PlatformBundle:Advert')
			->findAll();

		$formatted = [];
		foreach ($advert_list as $advert) {
			$dateCreation = $advert->getDate();

			$dateUpdate = $advert->getUpdatedAt();
			$dateUpdate = ((!empty($dateUpdate)) ? $dateUpdate->format('Y-m-d') : null);

			$categories = $advert->getCategories();
			$cat_list = [];
			if (!empty($categories))
				foreach ($categories as $cat)
					$cat_list[] = $cat->getName();

			$formatted[] = [
				'id' => $advert->getId(),
				'title' => $advert->getTitle(),
				'author' => $advert->getAuthor(),
				'content' => $advert->getContent(),
				'date' => $dateCreation->format('Y-m-d'),
				'updatedAt' => $dateUpdate,
				'published' => $advert->getPublished(),
				'categories' => $cat_list,
			];
		}

		return new JsonResponse($formatted);
	}

	/**
	 * @Rest\Get("/advert/{advert_id}")
	 * example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/advert/1
	 */
	public function getAdvertAction(Request $request)
	{
		$advert = $this->get('doctrine.orm.entity_manager')
			->getRepository('PlatformBundle:Advert')
			->find($request->get('advert_id'));

		if(empty($advert))
			return new JsonResponse(['message' => 'Advert not found'], Response::HTTP_NOT_FOUND);

		$dateCreation = $advert->getDate();

		$dateUpdate = $advert->getUpdatedAt();
		$dateUpdate = ((!empty($dateUpdate)) ? $dateUpdate->format('Y-m-d') : null);

		$categories = $advert->getCategories();
		$cat_list = [];
		if(!empty($categories))
			foreach($categories as $cat)
				$cat_list[] = $cat->getName();

		$formatted[] = [
			'id' => $advert->getId(),
			'title' => $advert->getTitle(),
			'author' => $advert->getAuthor(),
			'content' => $advert->getContent(),
			'date' => $dateCreation->format('Y-m-d'),
			'updatedAt' => $dateUpdate,
			'published' => $advert->getPublished(),
			'categories' => $cat_list,
		];

		return new JsonResponse($formatted);
	}

}
