<?php

namespace App\Form;

use App\Entity\Categories;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Catégorie',
            'attr' => [
                'placeholder' => 'Catégorie'
            ],
            'trim' => false,
            'constraints' => [
                new NotBlank(['message' => 'Veuillez préciser une nom']),
                new Length([
                    'min' => 5,
                    'minMessage' => 'Trop court, minimum {{ limit }} caractères',
                ]),
            ]
        ])
        ->add('parent', EntityType::class, [
            'class' => Categories::class,
            'choice_label' => 'nom',
            'placeholder' => 'Sélectionnez une catégorie',
            'label' => 'Catégorie parente',
            'required' => false,
            'label_attr' => [
                'class' => 'col-lg-12'
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
