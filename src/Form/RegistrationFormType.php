<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Pseudo',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un pseudo',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre pseudo est trop court minimum {{ limit }} caracères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ]
            ])
            ->add('poste', ChoiceType::class, [
                'choices' => [
                    'sélectionne une option' => '',
                    'Chauffeur poids lourd' => 'Chauffeur',
                    'Gérant de ferme' => 'Gerant',
                    'Simple agriculteur' => 'Agriculteur'
                ],
                'label' => 'Poste recherché',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new Email(['message' => 'Entrez un E-mail valide']),
                    new NotNull(['message' => 'Entrez un E-mail']),
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'always_empty' => false,
                'label' => 'Mot de passe*',
                'constraints' => [
                    new Regex([
                        'pattern' => '/[a-z]/',
                        'message' => 'Au moins une lettre minuscule'
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Au moins une lettre majuscule'
                    ]),
                    new Regex([
                        'pattern' => '/[1-9]/',
                        'message' => 'Au moins un chiffre'
                    ]),
                    new Length([
                        'min' => 14,
                        'minMessage' => 'Au moins {{ limit }} caractères',
                    ]),
                    new NotCompromisedPassword(['message' => 'Ce mot de passe est compromis']),
                ],
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'mapped' => false,
                'label_attr' => [
                    'class' => 'col-lg-12'
                ]
            ])
            ->add('avatar', FileType::class, [
                'attr' => [
                    'is' => 'drop-files',
                    'label' => 'Déposez votre photo ou cliquez pour ajouter.',
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
                ],
                'constraints' => [
                    new NotNull(['message' => 'Entrez une description']),
                    new Length([
                        'min' => 50,
                        'minMessage' => 'Au moins {{ limit }} caractères',
                    ]),
                ]
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
