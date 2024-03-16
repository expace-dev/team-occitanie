<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UserInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('poste', ChoiceType::class, [
                'choices' => [
                    'sélectionne une option' => '',
                    'Chauffeur poids lourd' => 'Chauffeur',
                    'Ouvrier agricole' => 'Ouvrier agricole',
                    'Chauffeur et agriculteur' => 'Chauffeur et agriculteur'
                ],
                'label' => 'Poste recherché',
                'label_attr' => [
                    'class' => 'col-lg-12'
                ],
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
