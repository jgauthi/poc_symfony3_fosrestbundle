<?php

namespace App\Service;

use Swift_Mailer;

class Antispam
{
    private $mailer;
    private $locale;
    private $minLength;

    public function __construct(Swift_Mailer $mailer, int $minLength)
    {
        $this->mailer = $mailer;
        $this->minLength = $minLength;
    }

    /**
     * Check if the text is spam or not.
     *
     * @param string $text
     *
     * @return bool
     */
    public function isSpam(string $text): bool
    {
        return mb_strlen($text) < $this->minLength;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
