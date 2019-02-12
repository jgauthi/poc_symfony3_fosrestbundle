<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Credentials
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    protected $login;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    protected $password;

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return Credentials
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Credentials
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
