<?php
namespace MyRestBundle\Entity;

class Credentials
{
    protected $login;

    protected $password;

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): Credentials
    {
        $this->login = $login;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): Credentials
    {
        $this->password = $password;
        return $this;
    }
}