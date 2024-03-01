<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Taches;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TachesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'Description de la tâche',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ]
            ])
            ->add('delai', TextType::class, [
                'label' => 'A effectuer avant',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
                'mapped' => false
            ])
            ->add('map', ChoiceType::class, [
                'choices' => [
                    'sélectionne une map' => '',
                    'Castelnaud' => 'Castelnaud',
                    'Pallegney' => 'Pallegney',
                ],
                'label' => 'Sélectionner la map',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Taches::class,
        ]);
    }
}
