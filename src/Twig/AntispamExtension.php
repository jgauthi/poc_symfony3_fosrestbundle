<?php

namespace App\Twig;

use App\Service\Antispam;
use Twig_Extension;

class AntispamExtension extends Twig_Extension
{
    private $antispam;

    /**
     * AntispamExtension constructor.
     *
     * @param Antispam $antispam
     */
    public function __construct(Antispam $antispam)
    {
        $this->antispam = $antispam;
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function checkIfArgumentIsSpam(string $text): bool
    {
        return $this->antispam->isSpam($text);
    }

    /**
     * Twig will execute this method to find out which function(s) to adds our service.
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('checkIfSpam', [$this, 'checkIfArgumentIsSpam']),
        ];
    }

    /**
     * The getName() method identifies your Twig extension, it is required.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Antispam';
    }
}
