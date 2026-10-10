<?php

namespace App\Form;

use App\Entity\Shelf;
use App\Entity\SlotCard;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlotCardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroSlot')
            ->add('typeCarte')
            ->add('nombrePortsTotal')
            ->add('shelf', EntityType::class, [
                'class' => Shelf::class,
                'choice_label' => fn (Shelf $shelf) => sprintf(
                    '%s — Shelf n°%d',
                    $shelf->getSiteMSAN()?->getNomSite() ?? 'Site ?',
                    $shelf->getNumeroShelf()
                ),
                'placeholder' => 'Choisissez un shelf',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SlotCard::class,
        ]);
    }
}
