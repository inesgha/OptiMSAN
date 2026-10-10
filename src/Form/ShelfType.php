<?php

namespace App\Form;

use App\Entity\Shelf;
use App\Entity\SiteMSAN;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShelfType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroShelf')
            ->add('adresseIpSub')
            ->add('sitemsan', EntityType::class, [
                'class' => SiteMSAN::class,
                'choice_label' => fn (SiteMSAN $site) => sprintf('%s (%s)', $site->getNomSite(), $site->getAdresseIp()),
                'placeholder' => 'Choisissez un site',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Shelf::class,
        ]);
    }
}
