<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UserInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ]
            ])
            ->add('avatar', FileType::class, [
                'attr' => [
                    'is' => 'drop-files',
                    'label' => 'Dépose ta nouvelle photo ou clique pour ajouter.',
                    'help' => 'Seul les fichiers jpg jpeg et png sont accepté',
                ],
                'label' => 'Photo de profil',
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
            ->add('description', TextareaType::class, [
                'required' => true,
                'help' => 'Décrivez vous en quelques mots',
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('poste', ChoiceType::class, [
                'choices' => [
                    'sélectionne une option' => '',
                    'Chauffeur poids lourd' => 'Chauffeur',
                    'Gérant de ferme' => 'Gerant',
                    'Simple agriculteur' => 'Agriculteur'
                ],
                'label' => 'Poste'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
