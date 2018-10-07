<?php

namespace MyRestBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class AuthTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    // Durée de validité du token en secondes, 12h
    const TOKEN_VALIDITY_DURATION = 12 * 3600;

    public function createToken(Request $request, $providerKey)
    {
        $autorisedPaths = [
            'get_applications', // Liste applications
            'post_auth_tokens', // Création d'un token (connexion)
        ];

        $currentRoute = $request->attributes->get('_route');
        if (in_array($currentRoute, $autorisedPaths, true)) {
            return;
        }

        $authTokenHeader = $request->headers->get('X-Auth-Token');
        if (!$authTokenHeader) {
            throw new BadCredentialsException('X-Auth-Token header is required');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $authTokenHeader,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof AuthTokenUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of AuthTokenUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $authTokenHeader = $token->getCredentials();
        $authToken = $userProvider->getAuthToken($authTokenHeader);

        if (!$authToken || !$this->isTokenValid($authToken)) {
            throw new BadCredentialsException('Invalid authentication token');
        }

        $user = $authToken->getUser();
        $pre = new PreAuthenticatedToken(
            $user,
            $authTokenHeader,
            $providerKey,
            $user->getRoles()
        );

        // Nos utilisateurs n'ont pas de role particulier, on doit donc forcer l'authentification du token
        // TODO: A changer
        $pre->setAuthenticated(true);

        return $pre;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    // Vérifie la validité du token
    private function isTokenValid($authToken)
    {
        return (time() - $authToken->getCreatedAt()->getTimestamp()) < self::TOKEN_VALIDITY_DURATION;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // Si les données d'identification ne sont pas correctes, une exception est levée
        throw $exception;
    }
}
