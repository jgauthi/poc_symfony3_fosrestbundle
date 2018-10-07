<?php

namespace PlatformBundle\Service;

use PlatformBundle\Entity\Application;

class ApplicationMailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNewNotification(Application $application): void
    {
        $message = new \Swift_Message(
            'New application',
            'You have received a new application.'
        );

        $author = preg_replace('#[^a-z0-9_]*#i', '-', $application->getAdvert()->getAuthor());
        $message
            ->addTo($author.'@symfony.local')
            ->addFrom('admin@symfony.local')
        ;

        $this->mailer->send($message);
    }
}
