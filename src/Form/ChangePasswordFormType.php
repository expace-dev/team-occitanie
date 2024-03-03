<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
                'class' => "gen"
            ],
            'mapped' => false,
            'label_attr' => [
                'class' => 'col-lg-12'
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
