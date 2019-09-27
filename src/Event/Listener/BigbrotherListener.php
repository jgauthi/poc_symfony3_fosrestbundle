<?php
namespace App\Event\Listener;

use App\Email\BigbrotherMailer;
use App\Event\MessagePostEvent;

class BigbrotherListener
{
    protected $mailer;
    protected $listUsers = [];

    /**
     * BigbrotherListener constructor.
     *
     * @param BigbrotherMailer $mailer
     * @param $listUsers
     */
    public function __construct(BigbrotherMailer $mailer, array $listUsers)
    {
        $this->mailer = $mailer;
        $this->listUsers = $listUsers;
    }

    /**
     * @param MessagePostEvent $event
     */
    public function processMessage(MessagePostEvent $event): void
    {
        // Monitoring is activated if the author of the message is in the list
        if (\in_array($event->getUser()->getUsername(), $this->listUsers, true)) {
            $this->mailer->send($event->getMessage(), $event->getUser());
        }
    }
}
