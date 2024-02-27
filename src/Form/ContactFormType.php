<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'required' => true,
            'label' => 'Nom',
            'attr' => [
                'placeholder' => 'Nom'
            ],
            'constraints' => [
                new NotNull([
                    'message' => 'Entrez un Nom'
                ]),
                new Length([
                    'min' => 3,
                    'minMessage' => 'Trop court, minimum {{ limit }} caractères',
                    'max' => 30,
                    'maxMessage' => 'Trop long, maximum {{ limit }} caractères'

                ]),
                new Regex([
                    'pattern' => '/^([a-zA-Z]+)$/',
                    'message' => 'Uniquement des lettres'
                ])
            ]
        ])
        ->add('email', EmailType::class, [
            'required' => true,
            'label' => 'Email',
            'attr' => [
                'placeholder' => 'Email'
            ],
            'constraints' => [
                new Email(['message' => 'Entrez un Email valide']),
                new NotNull(['message' => 'Entrez un Email'])
            ]
        ])
        ->add('sujet', TextType::class, [
            'required' => true,
            'label' => 'Sujet',
            'attr' => [
                'placeholder' => 'Sujet'
            ],
            'constraints' => [
                new NotNull([
                    'message' => 'Entrez un Nom'
                ]),
                new Length([
                    'min' => 10,
                    'minMessage' => 'Trop court, minimum {{ limit }} caractères'
                ]),
            ]
        ])
        ->add('message', TextareaType::class, [
            'required' => true,
            'label' => 'Votre message',
            'attr' => [
                'placeholder' => 'Message',
                'rows' => 5
            ],
            'constraints' => [
                new NotNull([
                    'message' => 'Entrez un Nom'
                ]),
                new Length([
                    'min' => 50,
                    'minMessage' => 'Trop court, minimum {{ limit }} caractères',

                ]),
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
