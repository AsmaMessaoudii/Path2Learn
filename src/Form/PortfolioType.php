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
                'label' => 'Titre du Portfolio',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Portfolio Développeur Web',
                    'maxlength' => 150,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre du portfolio est obligatoire',
                    ]),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 150,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9À-ÿ\s\-_,.!?()\'"&]+$/u',
                        'message' => 'Le titre contient des caractères non autorisés',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez votre portfolio en détail...',
                    'rows' => 6,
                    'maxlength' => 2000,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description du portfolio est obligatoire',
                    ]),
                    new Assert\Length([
                        'min' => 20,
                        'max' => 2000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères',
                    ]),
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
        ]);
    }
}