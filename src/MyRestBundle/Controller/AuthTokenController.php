<?php
namespace MyRestBundle\Controller;

use FOS\RestBundle\{Controller\Annotations as Rest, View\View};
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use MyRestBundle\Form\CredentialsType;
use MyRestBundle\Entity\{AuthToken, Credentials};
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\{Response, Request};

class AuthTokenController extends Controller
{
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"auth-token"})
     * @Rest\Post("/auth-tokens")
     *
     * @ApiDoc(
     *    resource=true,
     *    section="Token",
     *    description="Create an authentication token",
     *    input="MyRestBundle\Form\CredentialsType.php",
     *    statusCodes = {
     *        201 = "Successful creation",
     *        400 = "Invalid form"
     *    },
     *    responseMap={
     *         201 = {"class"=AuthToken::class, "groups"={"auth-token"}},
     *         400 = { "class"=CredentialsType::class, "fos_rest_form_errors"=true, "name" = ""}
     *    }
     * )
     */
    public function postAuthTokensAction(Request $request): Object
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit($request->request->all());
        if(!$form->isValid())
            return $form;

        $em = $this->get('doctrine.orm.entity_manager');

        // Check USER
        $user = $em->getRepository('MyUserBundle:User')
            ->findOneBy(['username' => $credentials->getLogin()]);

        if(!$user || !$user->hasRole('ROLE_API_ACCESS'))
            return $this->invalidCredentials();

        // Check Password
        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());

        if(!$isPasswordValid)
            return $this->invalidCredentials();

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
    */
    public function removeAuthTokenAction(Request $request): void
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $authToken = $em->getRepository('MyRestBundle:AuthToken')
            ->find($request->get('id'));

        $connectedUser = $this->get('security.token_storage')->getToken()->getUser();

        if($authToken && $authToken->getUser()->getId() === $connectedUser->getId())
        {
            $em->remove($authToken);
            $em->flush();
        }
        else throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException();
    }
}
