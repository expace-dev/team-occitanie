<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Articles;
use App\Entity\Commentaires;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentairesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('contenu', TextareaType::class, [
            'attr' => [
                'placeholder' => 'Votre Commentaire*',
                'rows' => '6',
                'required' => true
            ],
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrer votre commentaire',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Votre commentaire est trop court, minimum {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('parentid', HiddenType::class, [
            'mapped' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaires::class,
        ]);
    }
}
