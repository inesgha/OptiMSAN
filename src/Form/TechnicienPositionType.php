<?php

namespace App\Form;

use App\Entity\Technicien;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

/**
 * Mise à jour de la position GPS du technicien (utilisée pour les pannes à proximité
 * et le calcul de distance lors d'une qualification de ligne).
 */
class TechnicienPositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('latitudeActuelle', NumberType::class, [
                'label' => 'Latitude (ex : 36.8065)',
                'scale' => 6,
                'constraints' => [
                    new NotNull(message: 'La latitude est obligatoire.', groups: ['Position']),
                    new Range(min: -90, max: 90, notInRangeMessage: 'La latitude doit être comprise entre {{ min }} et {{ max }}.', groups: ['Position']),
                ],
            ])
            ->add('longitudeActuelle', NumberType::class, [
                'label' => 'Longitude (ex : 10.1815)',
                'scale' => 6,
                'constraints' => [
                    new NotNull(message: 'La longitude est obligatoire.', groups: ['Position']),
                    new Range(min: -180, max: 180, notInRangeMessage: 'La longitude doit être comprise entre {{ min }} et {{ max }}.', groups: ['Position']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Technicien::class,
            // seuls les champs de position sont validés (les autres données du compte ne sont pas modifiées ici)
            'validation_groups' => ['Position'],
        ]);
    }
}
