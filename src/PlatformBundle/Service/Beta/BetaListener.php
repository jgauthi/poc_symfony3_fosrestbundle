<?php

namespace PlatformBundle\Service\Beta;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BetaListener
{
    // Processor
    protected $betaHTML;

    // The end date of the beta version:
    // - Before this date, we will display a countdown (D-3 for example)
    // - After this date, we will no longer display the "beta"
    protected $endDate;

    public function __construct(BetaHTMLAdder $betaHTML, $endDate)
    {
        $this->betaHTML = $betaHTML;
        $this->endDate = new \Datetime($endDate);
    }

    public function processBeta(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $remaingDays = $this->endDate->diff(new \DateTime())->days;
        if ($remaingDays <= 0) {
            return;
        }

        $response = $this->betaHTML->addBeta($event->getResponse(), $remaingDays);
        $event->setResponse($response);
    }
}
