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
use Symfony\Component\Form\Extension\Core\Type\FileType;  // Ajout
use Symfony\Component\Validator\Constraints\File;  // Ajout

class RessourcePedagogiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre *',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Guide d\'installation',
                    'autocomplete' => 'off'
                ],
                'required' => true
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type *',
                'choices' => [
                    'PDF' => 'PDF',
                    'Vidéo' => 'Vidéo',
                    'Lien' => 'Lien',
                    'Document' => 'Document',
                    'Présentation' => 'Présentation',
                    'Audio' => 'Audio',
                    'Image' => 'Image',
                    'Exercice' => 'Exercice'
                ],
                'attr' => [
                    'class' => 'form-select',
                    'onchange' => 'toggleFileUpload(this.value)'
                ],
                'placeholder' => 'Sélectionnez un type',
                'required' => true
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL (pour les liens externes)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://exemple.com/document.pdf',
                    'autocomplete' => 'off'
                ],
                'required' => false  // Rendre optionnel
            ])
            // NOUVEAU : Champ pour uploader un fichier
            ->add('file', FileType::class, [
                'label' => 'Fichier (PDF, Image, Vidéo, etc.)',
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.mp3,.wav,.txt'
                ],
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                            'audio/mpeg',
                            'audio/wav',
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, Word, Excel, PowerPoint, Image, Vidéo, Audio, Texte)'
                    ])
                ],
                'help' => 'Taille max : 10MB. Formats acceptés : PDF, Word, Excel, PowerPoint, Images (JPG, PNG, GIF, WebP), Vidéos (MP4, MOV), Audio (MP3, WAV), Texte'
            ])
            ->add('cours', EntityType::class, [
                'label' => 'Cours associé *',
                'class' => Cours::class,
                'choice_label' => function(Cours $cours) {
                    return $cours->getTitre() . ' (' . $cours->getMatiere() . ')';
                },
                'placeholder' => 'Sélectionnez un cours',
                'attr' => [
                    'class' => 'form-select'
                ],
                'required' => true
            ])
            ->add('dateAjout', DateType::class, [
                'label' => 'Date d\'ajout *',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime())->format('Y-m-d')
                ],
                'data' => new \DateTime(),
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RessourcePedagogique::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token_ressource',
            'csrf_token_id'   => 'ressource_item',
        ]);
    }
}