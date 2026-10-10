<?php

namespace App\Form;

use App\Entity\PortMSAN;
use App\Entity\SlotCard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PortMSANType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroPort')
            ->add('typePort')
            ->add('etatPort', ChoiceType::class, [
    'label' => 'État du port',
    'choices' => [
        'Libre' => 'libre',
        'Occupé' => 'occupe',
        'En panne' => 'panne',
        'Réservé' => 'reserve',
    ],
])
            ->add('dateAffectation')
            ->add('slotCard', EntityType::class, [
                'class' => SlotCard::class,
                'choice_label' => fn (SlotCard $slot) => sprintf(
                    '%s — Shelf n°%d — Slot n°%d (%s, %d ports)',
                    $slot->getShelf()?->getSiteMSAN()?->getNomSite() ?? 'Site ?',
                    $slot->getShelf()?->getNumeroShelf() ?? 0,
                    $slot->getNumeroSlot(),
                    $slot->getTypeCarte(),
                    $slot->getNombrePortsTotal()
                ),
                'placeholder' => 'Choisissez une carte',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PortMSAN::class,
        ]);
    }
}
