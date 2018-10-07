<?php

namespace PlatformBundle\Validator;

use Doctrine\ORM\{EntityManager, EntityManagerInterface};
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\{Constraint, ConstraintValidator};

class AntifloodValidator extends ConstraintValidator
{
    private $requestStack;
    private $em;

    public function __construct(RequestStack $requestStack, EntityManager $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint): void
    {
        // $request = $this->requestStack->getCurrentRequest();
        // $ip = $request->getClientIp();

        $lastAdvert = $this
            ->em
            ->getRepository('PlatformBundle:Advert')
            ->getLastAdverts(1);

        $isFlood = false;
        if (!empty($lastAdvert[0])) {
            $currentDate = new \DateTime();
            $date = $lastAdvert[0]->getDate();

            $diff = $date->diff($currentDate);
            if ($diff->y === 0 && $diff->m === 0 && $diff->d === 0 && $diff->s < 15) {
                $isFlood = true;
            }
        }

        if ($isFlood) {
            $this->context->addViolation($constraint->message);
        }
    }
}
