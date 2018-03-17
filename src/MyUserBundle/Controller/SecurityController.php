<?php

namespace MyUserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED'))
            return $this->redirectToRoute('oc_platform_home');

        $authenticationUtils = $this->get('security.authentication_utils');

        return $this->render('@MyUser/login.html.twig', array
        (
            'last_username' =>  $authenticationUtils->getLastUsername(),
            'error'         =>  $authenticationUtils->getLastAuthenticationError(),
        ));
    }
}