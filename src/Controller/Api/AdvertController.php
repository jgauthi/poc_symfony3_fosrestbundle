<?php

namespace App\Controller\Api;

use App\Entity\{Advert, Category};
use App\Form\AdvertTypeApi;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\{
    Controller\Annotations as Rest,
    Request\ParamFetcher,
    View\View,
};
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};

/**
 * Class AdvertController
 * @package App\Controller\Api
 * @Rest\NamePrefix(value="api_")
 */
class AdvertController extends AbstractController
{
    /**
     * @Rest\View(serializerGroups={"advert", "advert_additional_info", "category"})
     * @Rest\Get("/advert/{id}")
     * @ApiDoc(
     *     resource=true,
     *     section="Advert",
     *     description="Récupère une annonce",
     *     output="App\Entity\Advert",
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
     * @param Request $request
     * @param AdvertRepository $advertRepository
     * @return Advert
     * exampleUrl: http://localhost:8000/fr/api/v1/advert/1
     */
	public function getAdvertAction(Request $request, AdvertRepository $advertRepository): Advert
	{
        /** @var Advert $advert */
        $advert = $advertRepository->findOneBy(['id' => $request->get('id')]);

		if(empty($advert)) {
            throw $this->createNotFoundException('Advert not found');
        }

        return $advert;
	}

    /**
     * @Rest\View(serializerGroups={"advert"})
     * @Rest\Get("/adverts")
     * @Rest\QueryParam(name="offset", requirements="\d+", default="", description="Index début pagination")
     * @Rest\QueryParam(name="limit", requirements="\d+", default="", description="Index de fin de pagination")
     * @Rest\QueryParam(name="order", requirements="(asc|desc)", nullable=true, description="Ordre de trie (basé sur le titre)")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Advert",
     *     description="Get the list of adverts",
     *     output= { "class"=Advert::class, "collection"=true, "groups"={"advert"} },
     *     headers={
     *         { "name"="X-Auth-Token", "required"=true, "description"="Authorization key" },
     *    }
     * )
     * @param ParamFetcher $paramFetcher
     * @param EntityManagerInterface $em
     * @return array
     * exampleUrl: http://localhost:8000/fr/api/v1/adverts?offset=1&limit=3&order=desc
     */
    public function getAdvertsAction(ParamFetcher $paramFetcher, EntityManagerInterface $em): array
    {
        // With the QueryParam annotation, the Param Fetcher Listener automatically injects the parametcher to our method
        $offset = $paramFetcher->get('offset');
        $limit = $paramFetcher->get('limit');
        $orderByTitle = $paramFetcher->get('order');

        $qb = $em->createQueryBuilder();
        $qb->select('advert')->from(Advert::class, 'advert');

        if(!empty($offset)) {
            $qb->setFirstResult($offset);
        }

        if(!empty($limit)) {
            $qb->setMaxResults($limit);
        }

        if(!empty($orderByTitle) && in_array($orderByTitle, ['asc', 'desc'])) {
            $qb->orderBy('advert.title', $orderByTitle);
        }

        $advert_list = $qb->getQuery()->getResult();


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
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"advert", "category"})
     * @Rest\Post("/advert")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Advert",
     *     description="Ajouter une annonce",
     *     input="App\Form\AdvertType",
     *     statusCodes = {
     *        201 = "Création avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         201 = {"class"=Advert::class, "groups"={"advert"}},
     *         400 = { "class"=Advert::class, "form_errors"=true, "name" = ""}
     *    },
     *    headers={
     *         { "name"="X-Auth-Token", "required"=true, "description"="Authorization key" },
     *    }
     * )
     * Example-JSON-Send-to-API:
     * {
     *      "title": "Publication via API Raw",
     *      "content": "Lorem ipsu dolor color...",
     *      "author": "JohnDoe",
     *      "categories": [
     *          "Développement web",
     *          "Dev OPS",
     *          "Intégration"
     *      ]
     * }
     * @param Request $request
     * @return Object
     */
    public function postAdvertAction(Request $request): Object
    {
        $advert = new Advert();
        $em = $this->getDoctrine()->getManager();

        $post_data = $request->request->all();
        if(isset($post_data['categories'])) {
            $categories = $post_data['categories'];
            unset($post_data['categories']);
        }
        else $categories = null;

        $form = $this->createForm(AdvertTypeApi::class, $advert);
        $form->submit($post_data); // Validation des données
        if(!$form->isValid()) {
            return $form;
        }

        // Announcement must be confirmed by an admin
        $advert->setPublished(false);

        $catRepo = $em->getRepository(Category::class);
        if(!empty($categories) && is_array($categories)) {
            foreach($categories as $catName) {
                $category = $catRepo->findOneBy(['name' => $catName]);
                if(empty($category)) {
                    $category = new Category();
                    $category->setName($catName);
                    $em->persist($category);
                }

                $advert->addCategory($category);
            }
        }

        $em->persist($advert);
        $em->flush();

        return $advert;
    }

    /**
     * @Rest\View(serializerGroups={"advert"})
     * @Rest\Put("/advert/{id}")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Advert",
     *     description="Modifier une annonce",
     *     input="App\Form\AdvertType",
     *     statusCodes = {
     *        201 = "Modification avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         201 = {"class"=Advert::class, "groups"={"advert"}},
     *         400 = { "class"=Advert::class, "form_errors"=true, "name" = ""}
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
     * @param Request $request
     * @return Object
     */
    public function updateAdvertAction(Request $request): Object
    {
        return $this->updateAdvert($request, true);
    }

    /**
     * @Rest\View(serializerGroups={"advert"})
     * @Rest\Patch("/advert/{id}")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Advert",
     *     description="Modifier certains champs d'une annonce",
     *     input="App\Form\AdvertType",
     *     statusCodes = {
     *        201 = "Modification avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         201 = {"class"=Advert::class, "groups"={"advert"}},
     *         400 = { "class"=Advert::class, "form_errors"=true, "name" = ""}
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
     * @param Request $request
     * @return Object
     */
    public function patchAdvertAction(Request $request): Object
    {
        return $this->updateAdvert($request, false);
    }

    /**
     * @param Request $request
     * @param $allFieldsRequire
     * @return Object
     */
    private function updateAdvert(Request $request, bool $allFieldsRequire): Object
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository(Advert::class)->find($request->get('id'));

        if(empty($advert)) {
            throw $this->createNotFoundException('Advert not found');
        }

        $form = $this->createForm(AdvertTypeApi::class, $advert);
        $form->submit($request->request->all(), $allFieldsRequire);

        if(!$form->isValid()) {
            return $form;
        }

        $em->merge($advert);
        $em->flush();

        return $advert;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"advert"})
     * @Rest\Delete("/advert/{id}")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Advert",
     *     description="Modifier une annonce",
     *     input="App\Form\AdvertType",
     *     statusCodes = {
     *        200 = "Suppression avec succès",
     *        400 = "Formulaire invalide"
     *    },
     *    responseMap={
     *         400 = { "class"=Advert::class, "form_errors"=true, "name" = ""}
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
     * @param Request $request
     * @return View
     */
    public function removeAdvertAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository(Advert::class)->find($request->get('id'));

        if(empty($advert)) {
            throw $this->createNotFoundException('Advert not found');
        } elseif(true === $advert->getPublished()) {
            return View::create(['message' => 'You can\'t delete un published advert'], Response::HTTP_BAD_REQUEST);
        }

        $em->remove($advert);
        $em->flush();
    }
}
