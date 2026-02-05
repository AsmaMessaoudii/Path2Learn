<?php

namespace App\Form;

use App\Entity\RessourcePedagogique;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RessourcePedagogiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Guide d\'installation']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'PDF' => 'PDF',
                    'Vidéo' => 'VIDEO',
                    'Lien' => 'LIEN',
                    'Image' => 'IMAGE',
                    'Audio' => 'AUDIO'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://exemple.com/document.pdf']
            ])
            ->add('cours', EntityType::class, [
                'label' => 'Cours associé',
                'class' => Cours::class,
                'choice_label' => function(Cours $cours) {
                    return $cours->getTitre() . ' (' . $cours->getMatiere() . ')';
                },
                'placeholder' => 'Sélectionnez un cours',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RessourcePedagogique::class,
        ]);
    }
}