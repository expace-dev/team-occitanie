<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('username')
            ->add('description', TextareaType::class, [
                'required' => true,
                'help' => 'Décrivez vous en quelques mots',
                'attr' => [
                    'rows' => 10
                ]
            ])
            ->add('poste', ChoiceType::class, [
                'choices' => [
                    'Chauffeur poids lourd' => 'Chauffeur',
                    'Gérant de ferme' => 'Gerant',
                    'Simple agriculteur' => 'Agriculteur'
                ],
                'label' => 'Poste'
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [ 
                    '' => '',
                    'Administrateur' => 'ROLE_ADMIN', 
                    'Joueur'=>'ROLE_USER',
                    'Modérateur' => 'ROLE_MODO',
                    'Editeur' => 'ROLE_EDIT'
                ],
                'label' => 'Fonction'
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
