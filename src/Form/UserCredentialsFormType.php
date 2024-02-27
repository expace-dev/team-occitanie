<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserCredentialsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ancienPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
                'always_empty' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'validate',
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'always_empty' => false,
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
                'label' => 'Nouveau mot de passe',
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
                        'pattern' => '/[0-9]/',
                        'message' => 'Au moins un chiffre'
                    ]),
                    new Length([
                        'min' => 14,
                        'minMessage' => 'Au moins {{ limit }} caractères',
                    ]),
                ],
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'mapped' => false
            ])
            ->add('email', EmailType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
