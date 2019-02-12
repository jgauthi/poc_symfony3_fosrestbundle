<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

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
     * @Groups({"auth-token"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Groups({"auth-token"})
     */
    protected $value;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"auth-token"})
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Entity\User")
     * @Groups({"auth-token"})
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

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }
}
