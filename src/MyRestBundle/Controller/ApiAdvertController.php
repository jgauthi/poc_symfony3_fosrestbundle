<?php

namespace MyRestBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use MyRestBundle\Form\AdvertType;
use PlatformBundle\Entity\Advert;
use PlatformBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAdvertController extends Controller
{
	/**
	 * @Rest\View(serializerGroups={"advert"})
     * @Rest\Get("/adverts")
	 * example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/adverts
	 */
	public function getAdvertsAction()
	{
		$advert_list = $this->get('doctrine.orm.entity_manager')
			->getRepository('PlatformBundle:Advert')
			->findAll();

		/*$formatted = [];
		foreach($advert_list as $advert) {
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

		// Utilisation du view handler du bundle Fos rest
        $viewHandler = $this->get('fos_rest.view_handler');
		$view = View::create($advert_list);
		$view->setFormat('json');

		return $viewHandler->handle($view);

		}*/

		// To avoid "circular reference error", add on entity class:
        // use Symfony\Component\Serializer\Annotation\MaxDepth;
        // @MaxDepth(1) on get method who return collection value

		return $advert_list;
	}

	/**
     * @Rest\View(serializerGroups={"advert", "advert_additional_info"})
	 * @Rest\Get("/advert/{advert_id}")
	 * example url: http://localhost/mindsymfony/web/app_dev.php/fr/api/v1/advert/1
	 */
	public function getAdvertAction(Request $request)
	{
		$advert = $this->get('doctrine.orm.entity_manager')
			->getRepository('PlatformBundle:Advert')
			->find($request->get('advert_id'));

		if(empty($advert))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Advert not found');

        return $advert;
	}

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"advert"})
     * @Rest\Post("/advert")
     * @Example-JSON-Send-to-API:
        {
            "title": "Publication via API Raw",
            "content": "Lorem ipsu dolor color...",
            "author": "JohnDoe",
            "categories": [
                "Développement web",
                "Dev OPS",
                "Intégration"
            ]
        }
     */
    public function postAdvertAction(Request $request)
    {
        $advert = new Advert();
        $em = $this->get('doctrine.orm.entity_manager');

        $post_data = $request->request->all();
        if(isset($post_data['categories']))
        {
            $categories = $post_data['categories'];
            unset($post_data['categories']);
        }
        else $categories = null;

        $form = $this->createForm(AdvertType::class, $advert);
        $form->submit($post_data); // Validation des données

        if($form->isValid())
        {
            // Annonce à confirmer par un admin
            $advert->setPublished(false);

            $catRepo = $em->getRepository('PlatformBundle:Category');
            if(!empty($categories) && is_array($categories)) foreach($categories as $catName)
            {
                $category = $catRepo->findOneBy(['name' => $catName]);
                if(empty($category))
                {
                    $category = new Category();
                    $category->setName($catName);
                    $em->persist($category);
                }

                $advert->addCategory($category);
            }

            $em->persist($advert);
            $em->flush();

            return $advert;
        }
        else return $form;
    }

    /**
     * @Rest\View(serializerGroups={"advert"})
     * @Rest\Put("/advert/{id}")
     */
    public function updateAdvertAction(Request $request)
    {
        return $this->updateAdvert($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"advert"})
     * @Rest\Patch("/advert/{id}")
     */
    public function patchAdvertAction(Request $request)
    {
        return $this->updateAdvert($request, false);
    }

    private function updateAdvert(Request $request, $allFieldsRequire)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $advert = $em->getRepository('PlatformBundle:Advert')->find($request->get('id'));

        if(empty($advert))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Advert not found');

        $form = $this->createForm(AdvertType::class, $advert);
        $form->submit($request->request->all(), $allFieldsRequire);

        if($form->isValid())
        {
            $em->merge($advert);
            $em->flush();

            return $advert;
        }
        else return $form;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"advert"})
     * @Rest\Delete("/advert/{id}")
    */
    public function removeAdvertAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $advert = $em->getRepository('PlatformBundle:Advert')
            ->find($request->get('id'));

        if(empty($advert))
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Advert not found');

        elseif(true === $advert->getPublished())
            return View::create(['message' => 'You can\'t delete un published advert'], Response::HTTP_BAD_REQUEST);

        $em->remove($advert);
        $em->flush();
    }
}
