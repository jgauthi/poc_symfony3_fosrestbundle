<?php
namespace PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Antiflood extends Constraint
{
    public $message = "You have already posted a message less than 15 seconds ago, please wait a bit.";

    // Utilisation du service
    public function validatedBy(): string
    {
        return 'platform_antiflood';
    }
}
