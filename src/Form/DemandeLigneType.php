<?php

namespace App\Form;

use App\Entity\DemandeLigne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\Length;

class DemandeLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeOffre', ChoiceType::class, [
                'label' => 'Type d\'offre souhaitée',
                'choices' => [
                    'ADSL Basique' => 'adsl_basique',
                    'VDSL Fibre' => 'vdsl_fibre',
                    'Fibre Optique (FTTH)' => 'fibre_ftth',
                    'Ligne téléphonique seule' => 'telephone_seul',
                ],
                'placeholder' => 'Sélectionnez une offre',
            ])
            ->add('adresseInstallation', TextType::class, [
                'label' => 'Adresse d\'installation',
                'attr' => ['placeholder' => 'Adresse complète où la ligne doit être installée', 'maxlength' => 150],
                'constraints' => [
                    new Length(min: 5, minMessage: 'L\'adresse d\'installation doit contenir au moins {{ limit }} caractères.'),
                ],
            ])
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeLigne::class,
        ]);
    }
}