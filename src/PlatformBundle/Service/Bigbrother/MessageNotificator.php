<?php

namespace PlatformBundle\Service\Bigbrother;

use Symfony\Component\Security\Core\User\UserInterface;

class MessageNotificator
{
    protected $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    // Method to notify an administrator by e-mail
    public function notifyByEmail(string $message, UserInterface $user): void
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('New message from BigDaddy')
            ->setFrom('bigbrother@symfony.local')
            ->setTo('admin@symfony.local')
            ->setBody(sprintf('"The monitored user %s posted the following message: "%s"', $user->getUsername(), $message))
        ;

        $this->mailer->send($message);
    }
}
