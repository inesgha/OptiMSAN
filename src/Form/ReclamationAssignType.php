<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Technicien;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationAssignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('technicien', EntityType::class, [
                'class' => Technicien::class,
                'choice_label' => function (Technicien $technicien) {
                    return sprintf('%s %s (%s)', $technicien->getPrenom(), $technicien->getNom(), $technicien->getMatricule());
                },
                'label' => 'Assigner au technicien',
                'placeholder' => 'Non assigné',
                'required' => false,
            ])
            ->add('etatTicket', ChoiceType::class, [
                'label' => 'État du ticket',
                'choices' => [
                    'Ouvert' => 'ouvert',
                    'En cours' => 'en_cours',
                    'Résolu' => 'resolu',
                    'Fermé' => 'ferme',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}