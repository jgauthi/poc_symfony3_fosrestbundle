<?php
namespace MyRestBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use MyRestBundle\Form\CredentialsType;
use MyRestBundle\Entity\AuthToken;
use MyRestBundle\Entity\Credentials;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AuthTokenController extends Controller
{
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"auth-token"})
     * @Rest\Post("/auth-tokens")
     */
    public function postAuthTokensAction(Request $request)
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

    private function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }
}
