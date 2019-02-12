<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthTokenRepository")
 * @ORM\Table(name="user_auth_tokens", uniqueConstraints={@ORM\UniqueConstraint(name="auth_tokens_value_unique", columns={"value"})}
 * )
 */
class AuthToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\User")
     *
     * @var User
     */
    protected $user;

    /**
     * Get name (for EasyAdminBundle)
     */
    public function __toString(): string
    {
        return sprintf('Token %s, User: %s', $this->getValue(), $this->getUser()->getUsername());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): AuthToken
    {
        $this->id = $id;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue($value): AuthToken
    {
        $this->value = $value;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): AuthToken
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): AuthToken
    {
        $this->user = $user;

        return $this;
    }
}
