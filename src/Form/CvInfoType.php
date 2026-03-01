<?php

namespace App\Form;

use App\Entity\CvInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CvInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: +216 20 123 456']
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Votre adresse complète']
            ])
            ->add('date_naissance', DateType::class, [
                'label' => 'Date de naissance',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('linkedin', UrlType::class, [
                'label' => 'Profil LinkedIn',
                'required' => false,
                'attr' => ['placeholder' => 'https://www.linkedin.com/in/...']
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => true,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF, WEBP)',
                    ])
                ],
            ])
            ->add('langues', CollectionType::class, [
                'label' => 'Langues',
                'entry_type' => LanguageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__name__',
                'attr' => ['class' => 'languages-collection']
            ])
            ->add('centres_interet', CollectionType::class, [
                'label' => 'Centres d\'intérêt',
                'entry_type' => InterestType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'prototype_name' => '__name__',
                'attr' => ['class' => 'interests-collection']
            ])
            ->add('certifications', TextareaType::class, [
                'label' => 'Certifications',
                'required' => false,
                'attr' => ['rows' => 4, 'placeholder' => '• Certification Symfony\n• Certification Python\n• ...']
            ])
            ->add('objectif', TextareaType::class, [
                'label' => 'Objectif professionnel',
                'required' => false,
                'attr' => ['rows' => 3, 'placeholder' => 'Développeur Full Stack passionné...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CvInfo::class,
        ]);
    }
}