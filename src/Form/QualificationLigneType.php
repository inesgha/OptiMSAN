<?php

namespace App\Form;

use App\Entity\PortMSAN;
use App\Entity\QualificationLigne;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualificationLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portMSAN', EntityType::class, [
                'class' => PortMSAN::class,
                'choice_label' => fn(PortMSAN $p) => sprintf('Port %d (%s)', $p->getNumeroPort(), $p->getEtatPort()),
                'label' => 'Port à tester',
            ])
            ->add('standard', ChoiceType::class, [
                'label' => 'Standard',
                'choices' => [
                    'VDSL2' => 'VDSL2',
                    'ADSL2+' => 'ADSL2+',
                    'ADSL2' => 'ADSL2',
                    'ADSL' => 'ADSL',
                ],
            ])
            ->add('snrMarginDown', NumberType::class, ['label' => 'Marge SNR descendante (dB)'])
            ->add('snrMarginUp', NumberType::class, ['label' => 'Marge SNR montante (dB)'])
            ->add('attenuationDown', NumberType::class, ['label' => 'Atténuation descendante (dB)'])
            ->add('attenuationUp', NumberType::class, ['label' => 'Atténuation montante (dB)'])
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