<?php

namespace App\Controller\Api;

use App\Entity\{AuthToken, Credentials, User};
use App\Form\CredentialsType;
use FOS\RestBundle\{Controller\Annotations as Rest, View\View};
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

/**
 * Class AuthTokenController.
 *
 * @Rest\NamePrefix(value="api_")
 */
class AuthTokenController extends AbstractController
{
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"auth-token"})
     * @Rest\Post("/auth-tokens")
     *
     * @ApiDoc(
     *    resource=true,
     *    section="Token",
     *    description="Create an authentication token",
     *    input="App\Form\CredentialsType.php",
     *    statusCodes = {
     *        201 = "Successful creation",
     *        400 = "Invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=AuthToken::class, "groups"={"auth-token"}},
     *         400 = { "class"=CredentialsType::class, "fos_rest_form_errors"=true, "name" = ""}
     *    }
     * )
     *
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     *
     * @throws \Exception
     *
     * @return object
     */
    public function postAuthTokensAction(Request $request, UserPasswordEncoderInterface $encoder): Object
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['username' => $credentials->getLogin()]);

        if (!$user || !$user->hasRole('ROLE_API_ACCESS')) {
            return $this->invalidCredentials();
        }

        // Check Password
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());
        if (!$isPasswordValid) {
            return $this->invalidCredentials();
        }

        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setUser($user);

        $em->persist($authToken);
        $em->flush();

        return $authToken;
    }

    private function invalidCredentials(): View
    {
        return View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/auth-tokens/{id}")
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Token",
     *     description="Supprimer un token lié à un utilisateur",
     *     statusCodes = {
     *          200 = "Suppression avec succès",
     *          400 = "Formulaire invalide"
     *    },
     *    requirements={
     *         {
     *             "name"="id",
     *             "dataType"="integer",
     *             "requirements"="\d+",
     *             "description"="Identifiant du token"
     *         }
     *    },
     *    headers={
     *         { "name"="X-Auth-Token", "required"=true, "description"="Authorization key" },
     *    }
     * )
     *
     * @param Request               $request
     * @param TokenStorageInterface $tokenStorage
     */
    public function removeAuthTokenAction(Request $request, TokenStorageInterface $tokenStorage): void
    {
        $em = $this->getDoctrine()->getManager();
        $authToken = $em->getRepository(AuthToken::class)->find($request->get('id'));

        $connectedUser = $tokenStorage->getToken($authToken)->getUser();

        if ($authToken && $authToken->getUser()->getId() === $connectedUser->getId()) {
            $em->remove($authToken);
            $em->flush();
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
        }
    }
}
