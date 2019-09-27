<?php
namespace App\Email;

use Swift_Mailer;
use Twig_Environment;

abstract class AbstractMailer
{
    const DOMAIN_MAIL = 'symfony.local';

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * AbstractMailer constructor.
     *
     * @param Swift_Mailer     $mailer
     * @param Twig_Environment $twig
     */
    public function __construct(Swift_Mailer $mailer, Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    // abstract public function send(...$args): ?int;
}
