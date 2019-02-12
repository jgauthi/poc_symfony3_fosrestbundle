<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FosUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends FosUser implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"user", "preference", "auth-token"})
     */
    protected $id;

    /**
     * @var string
     * @Groups({"user", "preference", "auth-token"})
     */
    protected $username;

    /**
     * @var string
     * @Groups({"user", "preference", "auth-token"})
     */
    protected $email;

    /**
     * @var array
     * @Groups({"user"})
     */
    protected $roles;
}
