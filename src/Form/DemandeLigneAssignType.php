<?php

namespace App\Form;

use App\Entity\DemandeLigne;
use App\Entity\PortMSAN;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeLigneAssignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portMSAN', EntityType::class, [
                'class' => PortMSAN::class,
                'choice_label' => function (PortMSAN $portMSAN) {
                    return sprintf('Port %d (%s)', $portMSAN->getNumeroPort(), $portMSAN->getEtatPort());
                },
                'label' => 'Affecter un port MSAN',
                'placeholder' => 'Non affecté',
                'required' => false,
            ])
            ->add('etatDemande', ChoiceType::class, [
                'label' => 'État de la demande',
                'choices' => [
                    'En attente' => 'en_attente',
                    'Validée' => 'validee',
                    'En cours d\'installation' => 'en_cours',
                    'Installée' => 'installee',
                    'Refusée' => 'refusee',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeLigne::class,
        ]);
    }
}