<?php
namespace App\Service;

class Antispam
{
    private $locale;
    private $minLength;

    public function __construct(int $minLength)
    {
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
