<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Règle de capacité d'une carte (SlotCard) :
 * - sur un PortMSAN : la carte choisie ne doit pas déjà contenir nombrePortsTotal ports ;
 * - sur une SlotCard : nombrePortsTotal ne peut pas être inférieur au nombre de ports existants.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CapaciteCarte extends Constraint
{
    public string $messageCartePleine = 'La carte n°{{ slot }} est pleine ({{ max }} ports maximum).';
    public string $messageTotalTropPetit = 'Cette carte contient déjà {{ count }} port(s) : le nombre total de ports ne peut pas être inférieur.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
