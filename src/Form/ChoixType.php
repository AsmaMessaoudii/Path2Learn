<?php

namespace App\Form;

use App\Entity\Choix;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoixType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextType::class, [
                'label' => 'Texte du choix',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le contenu du choix',
                ],
            ])
            ->add('estCorrect', CheckboxType::class, [
                'label' => 'Est correct ?',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ])
            ->add('question', EntityType::class, [
                'class' => Question::class,
                'choice_label' => 'titre', // plus lisible qu'id
                'label' => 'Question associée',
                'attr' => [
                    'class' => 'form-select',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Choix::class,
        ]);
    }
}
