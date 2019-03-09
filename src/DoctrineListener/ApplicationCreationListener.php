<?php
namespace App\DoctrineListener;

use App\Entity\Application;
use App\Email\NewApplicationMailer;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ApplicationCreationListener
{
    /**
     * @var NewApplicationMailer
     */
    private $applicationMailer;

    public function __construct(NewApplicationMailer $applicationMailer)
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

        $this->applicationMailer->send($entity);
    }
}
