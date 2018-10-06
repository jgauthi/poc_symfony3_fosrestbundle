<?php
namespace PlatformBundle\Service\Bigbrother;

use PlatformBundle\Event\MessagePostEvent;

class MessageListener
{
    protected $notificator;
    protected $listUsers = array();

    public function __construct(MessageNotificator $notificator, $listUsers)
    {
        $this->notificator = $notificator;
        $this->listUsers   = $listUsers;
    }

    public function processMessage(MessagePostEvent $event): void
    {
        // On active la surveillance si l'auteur du message est dans la liste
        if(in_array($event->getUser()->getUsername(), $this->listUsers))
            $this->notificator->notifyByEmail($event->getMessage(), $event->getUser());
    }
}