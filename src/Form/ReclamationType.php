<?php

namespace App\Form;

use App\Entity\PortMSAN;
use App\Entity\Reclamation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typePanne', ChoiceType::class, [
                'label' => 'Type de panne',
                'choices' => [
                    'Pas de connexion Internet' => 'coupure_internet',
                    'Connexion lente / instable' => 'connexion_lente',
                    'Ligne téléphonique coupée' => 'coupure_telephone',
                    'Problème équipement (box, modem)' => 'probleme_equipement',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'Sélectionnez le type de panne',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Décrivez le problème',
                'attr' => ['rows' => 5, 'maxlength' => 255, 'placeholder' => 'Expliquez le problème rencontré (10 à 255 caractères)...'],
                'constraints' => [
                    new NotBlank(message: 'Décrivez le problème rencontré.'),
                    new Length(
                        min: 10,
                        max: 255,
                        minMessage: 'La description doit contenir au moins {{ limit }} caractères.',
                        maxMessage: 'La description ne doit pas dépasser {{ limit }} caractères.'
                    ),
                ],
            ])
            ->add('priorite', ChoiceType::class, [
                'label' => 'Priorité ressentie',
                'choices' => [
                    'Faible' => 'faible',
                    'Normale' => 'normale',
                    'Urgente' => 'urgente',
                ],
            ])
            ->add('portMSAN', EntityType::class, [
                'class' => PortMSAN::class,
                'choice_label' => function (PortMSAN $portMSAN) {
                    return sprintf('Port %d (%s)', $portMSAN->getNumeroPort(), $portMSAN->getEtatPort());
                },
                'label' => 'Port concerné (si connu)',
                'placeholder' => 'Non spécifié',
                'required' => false,
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