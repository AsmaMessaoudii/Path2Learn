<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Portfolio;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreProjet', TextType::class, [
                'label' => 'Titre du projet',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Application de gestion de projet',
                    'maxlength' => 150,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre du projet est obligatoire',
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
            ->add('text', TextareaType::class, [
                'label' => 'Texte court',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description courte du projet (10-255 caractères)',
                    'rows' => 3,
                    'maxlength' => 255,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le texte court est obligatoire',
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 255,
                        'minMessage' => 'Le texte doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le texte ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez en détail votre projet... (au moins 30 caractères)',
                    'rows' => 6,
                    'maxlength' => 2000,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La description est obligatoire',
                    ]),
                    new Assert\Length([
                        'min' => 30,
                        'max' => 2000,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('technologies', TextType::class, [
                'label' => 'Technologies utilisées',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: HTML, CSS, JavaScript, PHP, Symfony, MySQL',
                    'maxlength' => 255,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Les technologies sont obligatoires',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Les technologies ne peuvent pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9À-ÿ\s\-,.()\/&+#]+$/u',
                        'message' => 'Les technologies contiennent des caractères non autorisés',
                    ]),
                ],
                'trim' => true,
            ])
            ->add('dateRealisation', DateType::class, [
                'label' => 'Date de réalisation',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => 'today'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de réalisation est obligatoire',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être dans le futur',
                    ]),
                ],
            ])
            ->add('lienDemo', UrlType::class, [
                'label' => 'Lien de démonstration',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://exemple.com/demo',
                    'maxlength' => 255,
                    'autocomplete' => 'off'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le lien de démonstration est obligatoire',
                    ]),
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le lien ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Assert\Url([
                        'message' => 'Veuillez entrer une URL valide (commençant par http:// ou https://)',
                        'protocols' => ['http', 'https'],
                    ]),
                ],
                'trim' => true,
            ])
            ->add('portfolio', EntityType::class, [
                'class' => Portfolio::class,
                'choice_label' => 'titre',
                'label' => 'Portfolio associé',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Le portfolio associé est obligatoire',
                    ]),
                ],
                'placeholder' => 'Sélectionnez un portfolio',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'projet_item',
            'validation_groups' => ['Default', 'creation'],
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}