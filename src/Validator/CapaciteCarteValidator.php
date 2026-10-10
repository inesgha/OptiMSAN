<?php

namespace App\Validator;

use App\Entity\PortMSAN;
use App\Entity\SlotCard;
use App\Repository\PortMSANRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CapaciteCarteValidator extends ConstraintValidator
{
    public function __construct(private PortMSANRepository $portRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CapaciteCarte) {
            throw new UnexpectedTypeException($constraint, CapaciteCarte::class);
        }

        if ($value instanceof PortMSAN) {
            $carte = $value->getSlotCard();
            if (null === $carte || null === $carte->getNombrePortsTotal()) {
                return;
            }

            $autresPorts = $this->portRepository->compterPortsSurCarte($carte, $value->getId());
            if ($autresPorts >= $carte->getNombrePortsTotal()) {
                $this->context->buildViolation($constraint->messageCartePleine)
                    ->setParameter('{{ slot }}', (string) $carte->getNumeroSlot())
                    ->setParameter('{{ max }}', (string) $carte->getNombrePortsTotal())
                    ->atPath('slotCard')
                    ->addViolation();
            }

            return;
        }

        if ($value instanceof SlotCard && null !== $value->getId() && null !== $value->getNombrePortsTotal()) {
            $ports = $this->portRepository->compterPortsSurCarte($value);
            if ($ports > $value->getNombrePortsTotal()) {
                $this->context->buildViolation($constraint->messageTotalTropPetit)
                    ->setParameter('{{ count }}', (string) $ports)
                    ->atPath('nombrePortsTotal')
                    ->addViolation();
            }
        }
    }
}
