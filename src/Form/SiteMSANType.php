<?php

namespace App\Form;

use App\Entity\SiteMSAN;
use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteMSANType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSite')
            ->add('adresseIp')
            ->add('typeMSAN')
            ->add('constructeur')
            ->add('latitude', HiddenType::class)
            ->add('longitude', HiddenType::class)
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'nomZone',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SiteMSAN::class,
        ]);
    }
}