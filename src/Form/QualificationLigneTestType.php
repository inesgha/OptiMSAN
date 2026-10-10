<?php

namespace App\Form;

use App\Entity\PortMSAN;
use App\Entity\QualificationLigne;
use App\Entity\Technicien;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class QualificationLigneTestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('technicien', EntityType::class, [
                'class' => Technicien::class,
                'choice_label' => fn(Technicien $t) => sprintf('%s %s (%s)', $t->getPrenom(), $t->getNom(), $t->getMatricule()),
                'label' => 'Technicien ayant réalisé le test',
            ])
            ->add('portMSAN', EntityType::class, [
                'class' => PortMSAN::class,
                'choice_label' => fn(PortMSAN $p) => sprintf('Port %d (%s)', $p->getNumeroPort(), $p->getEtatPort()),
                'label' => 'Port testé',
            ])
            ->add('standard', TextType::class, [
                'label' => 'Standard (ex: VDSL2, ADSL2+)',
            ])
            ->add('channelRateDown', NumberType::class, ['label' => 'Débit descendant (kbps)', 'constraints' => [new NotNull(message: 'Ce champ est obligatoire.')]])
            ->add('channelRateUp', NumberType::class, ['label' => 'Débit montant (kbps)', 'constraints' => [new NotNull(message: 'Ce champ est obligatoire.')]])
            ->add('snrMarginDown', NumberType::class, ['label' => 'Marge SNR descendante (dB)'])
            ->add('snrMarginUp', NumberType::class, ['label' => 'Marge SNR montante (dB)'])
            ->add('attenuationDown', NumberType::class, ['label' => 'Atténuation descendante (dB)'])
            ->add('attenuationUp', NumberType::class, ['label' => 'Atténuation montante (dB)'])
            ->add('distanceKm', NumberType::class, ['label' => 'Distance estimée (km)', 'constraints' => [new NotNull(message: 'Ce champ est obligatoire.')]])
            ->add('qualiteGlobale', ChoiceType::class, [
                'label' => 'Qualité globale',
                'choices' => [
                    'Excellente' => 'excellente',
                    'Bonne' => 'bonne',
                    'Moyenne' => 'moyenne',
                    'Faible' => 'faible',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QualificationLigne::class,
        ]);
    }
}