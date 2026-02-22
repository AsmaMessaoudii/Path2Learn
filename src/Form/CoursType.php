<?php

namespace App\Form;

use App\Entity\Cours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du cours *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Introduction à Symfony 6'
                ]
            ])
            ->add('matiere', TextType::class, [
                'label' => 'Matière *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Informatique'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description *',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Description détaillée du cours...'
                ]
            ])
            ->add('niveau', ChoiceType::class, [
                'label' => 'Niveau *',
                'choices' => [
                    'Débutant' => 'Débutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Avancé' => 'Avancé',
                    'Expert' => 'Expert'
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Sélectionnez un niveau'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes) *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 120'
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut *',
                'choices' => [
                    'Brouillon' => 'brouillon',
                    'Publié' => 'publié',
                    'Archivé' => 'archivé'
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('emailProf', EmailType::class, [
                'label' => 'Email du professeur *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'exemple@domain.com'
                ]
            ])
            ->add('dateCreation', DateType::class, [
                'label' => 'Date de création *',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}