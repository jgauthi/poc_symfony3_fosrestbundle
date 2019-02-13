<?php

namespace App\Service\Bigbrother;

use App\Event\MessagePostEvent;

class MessageListener
{
    protected $notificator;
    protected $listUsers = [];

    /**
     * MessageListener constructor.
     *
     * @param MessageNotificator $notificator
     * @param $listUsers
     */
    public function __construct(MessageNotificator $notificator, array $listUsers)
    {
        $this->notificator = $notificator;
        $this->listUsers = $listUsers;
    }

    /**
     * @param MessagePostEvent $event
     */
    public function processMessage(MessagePostEvent $event): void
    {
        // On active la surveillance si l'auteur du message est dans la liste
        if (\in_array($event->getUser()->getUsername(), $this->listUsers, true)) {
            $this->notificator->notifyByEmail($event->getMessage(), $event->getUser());
        }
    }
}
