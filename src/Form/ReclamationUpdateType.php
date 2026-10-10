<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etatTicket', ChoiceType::class, [
                'label' => 'État du ticket',
                'choices' => [
                    'Ouvert' => 'ouvert',
                    'En cours' => 'en_cours',
                    'Résolu' => 'resolu',
                    'Fermé' => 'ferme',
                ],
            ])
            ->add('notesIntervention', TextareaType::class, [
                'label' => 'Notes d\'intervention',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => 'Ajoutez des détails sur l\'intervention...'],
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