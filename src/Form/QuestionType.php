<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de la question',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre (minimum 5 caractères)',
                ],
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Durée en minutes (1-120)',
                ],
            ])
            ->add('noteMax', NumberType::class, [
                'label' => 'Note maximale',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Note maximale (1-100)',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Décrivez la question... (minimum 10 caractères)',
                ],
            ])
            ->add('form_name', HiddenType::class, [
                'data' => $builder->getName(),
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}