<?php

namespace PlatformBundle\DoctrineListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PlatformBundle\Entity\Application;
use PlatformBundle\Service\ApplicationMailer;

class ApplicationCreationListener
{
    /**
     * @var ApplicationMailer
     */
    private $applicationMailer;

    public function __construct(ApplicationMailer $applicationMailer)
    {
        $this->applicationMailer = $applicationMailer;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // We only want to send an email for Application entities
        if (!$entity instanceof Application) {
            return;
        }

        $this->applicationMailer->sendNewNotification($entity);
    }
}
