<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Evenements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class EvenementsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [])
            ->add('visuel', FileType::class, [
                'attr' => [
                    'is' => 'drop-files',
                    'label' => 'Déposez votre photo ou cliquez pour ajouter.',
                    'help' => 'Seul les fichiers jpg jpeg et png sont accepté',
                ],
                'label' => 'Image d\'illustration',
                'multiple' => false,
                'data_class' => null,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Seul les images sont acceptés'
                    ])
                ]
            ])
            ->add('dateEvents', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('typeSession', ChoiceType::class, [
                'choices' => [
                    'Sélectionne une option' => '',
                    'Convoi perso' => 'Convoi perso',
                    'Trucker MP' => 'Trucker MP',
                ],
                'label' => 'Sélectionner une option',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenements::class,
        ]);
    }
}
