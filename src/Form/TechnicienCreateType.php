<?php

namespace App\Form;

use App\Entity\Technicien;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TechnicienCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', TelType::class, ['required' => false])
            ->add('matricule', TextType::class)
            ->add('latitudeActuelle', NumberType::class, [
                'label' => 'Latitude (ex : 36.8065)',
                'scale' => 6,
                'constraints' => [new NotNull(message: 'La position GPS est obligatoire.')],
            ])
            ->add('longitudeActuelle', NumberType::class, [
                'label' => 'Longitude (ex : 10.1815)',
                'scale' => 6,
                'constraints' => [new NotNull(message: 'La position GPS est obligatoire.')],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6, 'max' => 4096]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Technicien::class]);
    }
}