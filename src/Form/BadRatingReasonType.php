<?php

namespace App\Form;

use App\Entity\BadRatingReason;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BadRatingReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'label' => 'Pourquoi cette note ?',
                'choices' => [
                    'Contenu trop difficile' => 'contenu_difficile',
                    'Contenu trop simple' => 'contenu_simple',
                    'Qualité du cours médiocre' => 'qualite_mediocre',
                    'Problèmes techniques' => 'problemes_techniques',
                    'Formateur peu clair' => 'formateur_peu_clair',
                    'Manque d\'exercices pratiques' => 'manque_exercices',
                    'Ressources insuffisantes' => 'ressources_insuffisantes',
                    'Autre' => 'autre'
                ],
                'expanded' => true,
                'multiple' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une raison'])
                ]
            ])
            ->add('customReason', TextareaType::class, [
                'label' => 'Précisez votre raison (si "Autre")',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Expliquez pourquoi vous avez donné cette note...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadRatingReason::class,
        ]);
    }
}