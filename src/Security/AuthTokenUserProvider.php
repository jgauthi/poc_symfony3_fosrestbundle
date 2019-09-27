<?php
namespace App\Security;

use App\Entity\AuthToken;
use App\Repository\{AuthTokenRepository, UserRepository};
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\{UserInterface, UserProviderInterface};

class AuthTokenUserProvider implements UserProviderInterface
{
    protected $authTokenRepository;
    protected $userRepository;

    /**
     * AuthTokenUserProvider constructor.
     *
     * @param AuthTokenRepository $authTokenRepository
     * @param UserRepository      $userRepository
     */
    public function __construct(AuthTokenRepository $authTokenRepository, UserRepository $userRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param $authTokenHeader
     *
     * @return AuthToken
     */
    public function getAuthToken($authTokenHeader): AuthToken
    {
        /** @var AuthToken $token */
        $token = $this->authTokenRepository->findOneBy(['value' => $authTokenHeader]);

        return $token;
    }

    /**
     * @param string $email
     *
     * @return UserInterface
     */
    public function loadUserByUsername($email): UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        return $user;
    }

    /**
     * Le systéme d'authentification est stateless, on ne doit donc jamais appeler la méthode refreshUser.
     *
     * @param UserInterface $user
     */
    public function refreshUser(UserInterface $user): void
    {
        throw new UnsupportedUserException();
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return 'App\Entity\User' === $class;
    }
}
