<?php

namespace PlatformBundle\Twig;

class AntispamExtension extends \Twig_Extension
{
    private $Antispam;

    public function __construct(Antispam $Antispam)
    {
        $this->Antispam = $Antispam;
    }

    public function checkIfArgumentIsSpam(string $text): bool
    {
        return $this->Antispam->isSpam($text);
    }

    // Twig will execute this method to find out which function(s) to adds our service
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('checkIfSpam', [$this, 'checkIfArgumentIsSpam']),
        ];
    }

    // The getName() method identifies your Twig extension, it is required
    public function getName(): string
    {
        return 'Antispam';
    }
}
