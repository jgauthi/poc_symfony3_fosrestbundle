<?php

namespace App\Validator;

use App\Entity\Advert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\{Constraint, ConstraintValidator};

class AntifloodValidator extends ConstraintValidator
{
    private $requestStack;
    private $em;

    /**
     * AntifloodValidator constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    /**
     * @param $value
     * @param Constraint $constraint
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        // $request = $this->requestStack->getCurrentRequest();
        // $ip = $request->getClientIp();

        /** @var Advert $lastAdvert */
        $lastAdvert = $this
            ->em
            ->getRepository(Advert::class)
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
