<?php
namespace PlatformBundle\Service\Beta;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BetaListener
{
    // Processeur
    protected $betaHTML;

    // La date de fin de la version bêta :
    // - Avant cette date, on affichera un compte à rebours (J-3 par exemple)
    // - Après cette date, on n'affichera plus le « bêta »
    protected $endDate;

    public function __construct(BetaHTMLAdder $betaHTML, $endDate)
    {
        $this->betaHTML = $betaHTML;
        $this->endDate = new \Datetime($endDate);
    }

    public function processBeta(FilterResponseEvent $event)
    {
        if(!$event->isMasterRequest())
            return;

        $remaingDays = $this->endDate->diff( new \DateTime())->days;
        if($remaingDays <= 0)
            return;

        $response = $this->betaHTML->addBeta( $event->getResponse(), $remaingDays );
        $event->setResponse($response);
    }
}