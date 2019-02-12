<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\{PreAuthenticatedToken, TokenInterface};
use Symfony\Component\Security\Core\Exception\{AuthenticationException, BadCredentialsException};
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\{AuthenticationFailureHandlerInterface, SimplePreAuthenticatorInterface};

class AuthTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    // Durée de validité du token en secondes: 2h
    const TOKEN_VALIDITY_DURATION = 2 * 3600;

    /**
     * @param Request $request
     * @param $providerKey
     */
    public function createToken(Request $request, $providerKey)
    {
        $authorisedPaths = [
            'api_get_applications', // Liste applications
            'api_post_auth_tokens', // Création d'un token (connexion)
        ];

        $currentRoute = $request->attributes->get('_route');
        if (in_array($currentRoute, $authorisedPaths, true)) {
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

    /**
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @param $providerKey
     * @return PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey): PreAuthenticatedToken
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
        $pre->setAuthenticated(true);

        return $pre;
    }

    /**
     * @param TokenInterface $token
     * @param $providerKey
     * @return bool
     */
    public function supportsToken(TokenInterface $token, $providerKey): bool
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * Vérifie la validité du token
     * @param $authToken
     * @return bool
     */
    private function isTokenValid($authToken): bool
    {
        return (time() - $authToken->getCreatedAt()->getTimestamp()) < self::TOKEN_VALIDITY_DURATION;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): void
    {
        // Si les données d'identification ne sont pas correctes, une exception est levée
        throw $exception;
    }
}
