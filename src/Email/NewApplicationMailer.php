<?php
namespace App\Email;

use App\Entity\Application;
use Swift_Message;

class NewApplicationMailer extends AbstractMailer
{
    /**
     * @param Application $application
     * @return int|null
     */
    public function send(Application $application): ?int
    {
        $message = new Swift_Message(
            'New application',
            'You have received a new application.'
        );

        $author = preg_replace('#[^a-z0-9_]+#i', '-', $application->getAdvert()->getAuthor());
        $message
            ->addTo($author.'@'.self::DOMAIN_MAIL)
            ->addFrom('admin@'.self::DOMAIN_MAIL)
        ;

        return $this->mailer->send($message);
    }
}
