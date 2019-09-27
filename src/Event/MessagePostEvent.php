<?php
namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

class MessagePostEvent extends Event
{
    protected $message;
    protected $user;

    public function __construct(string $message, UserInterface $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    // The listener must have access to the message
    public function getMessage(): string
    {
        return $this->message;
    }

    // The listener must be able to modify the message
    public function setMessage(string $message): string
    {
        return $this->message = $message;
    }

    // The listener must have access to the user
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    // No setUser, listeners can not change the author of the message!
}
