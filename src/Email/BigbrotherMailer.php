<?php
namespace App\Email;

use Swift_Message;
use Symfony\Component\Security\Core\User\UserInterface;

class BigbrotherMailer extends AbstractMailer
{
    /**
     * Method to notify an administrator by e-mail.
     *
     * @param string        $message
     * @param UserInterface $user
     *
     * @return int|null
     */
    public function send(string $message, UserInterface $user): ?int
    {
        $message = Swift_Message::newInstance()
            ->setSubject('New message from BigDaddy')
            ->setFrom('bigbrother@'.self::DOMAIN_MAIL)
            ->setTo('admin@'.self::DOMAIN_MAIL)
            ->setBody(sprintf('"The monitored user %s posted the following message: "%s"', $user->getUsername(), $message))
        ;

        return $this->mailer->send($message);
    }
}
