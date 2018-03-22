<?php
namespace PlatformBundle\Service\Antispam;

class AntispamExtension extends \Twig_Extension
{
    private $Antispam;

    public function __construct(Antispam $Antispam)
    {
        $this->Antispam = $Antispam;
    }

    public function checkIfArgumentIsSpam($text)
    {
        return $this->Antispam->isSpam($text);
    }

    // Twig va exécuter cette méthode pour savoir quelle(s) fonction(s) ajoute notre service
    public function getFunctions()
    {
        return array
        (
            new \Twig_SimpleFunction('checkIfSpam', array($this, 'checkIfArgumentIsSpam')),
        );
    }

    // La méthode getName() identifie votre extension Twig, elle est obligatoire
    public function getName()
    {
        return 'Antispam';
    }
}