<?php
namespace App\Email;

use App\Entity\Application;
use App\Utils\Text;
use Swift_Message;

class ApplicationCreationMailer extends AbstractMailer
{
    /**
     * @param Application $application
     *
     * @return int|null
     */
    public function send(Application $application): ?int
    {
        $message = new Swift_Message(
            'New application',
            'You have received a new application.'
        );

        $author = Text::slugify($application->getAdvert()->getAuthor());
        $message
            ->addTo($author.'@'.self::DOMAIN_MAIL)
            ->addFrom('admin@'.self::DOMAIN_MAIL)
        ;

        return $this->mailer->send($message);
    }
}
