<?php

namespace App\Form;

use App\Entity\Portfolio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PortfolioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du portfolio *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Portfolio Développeur Web Full-Stack',
                    'maxlength' => 150,
                ],
                'trim' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez votre parcours, vos compétences, vos objectifs...',
                    'rows' => 6,
                    'maxlength' => 2000,
                ],
                'trim' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Portfolio::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'portfolio_item',
            'attr' => ['novalidate' => 'novalidate'], 
        ]);
    }
}