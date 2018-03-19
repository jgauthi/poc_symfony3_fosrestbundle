<?php
namespace PlatformBundle\Antispam;

class Antispam
{
    private $mailer;
    private $locale;
    private $minLength;

    public function __construct(\Swift_Mailer $mailer, $minLength)
    {
        $this->mailer    = $mailer;
        $this->minLength = (int) $minLength;
    }

    /**
     * Vérifie si le texte est un spam ou non
     *
     * @param string $text
     * @return bool
     */
    public function isSpam($text)
    {
        return strlen($text) < $this->minLength;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}


?>