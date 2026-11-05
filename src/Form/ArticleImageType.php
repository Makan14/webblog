<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ArticleImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Modification du texte alternatif et du statut principal d'une image.
 */
final class ArticleImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('alt', TextType::class, ['label' => 'form.alt', 'required' => false])
            ->add('principale', CheckboxType::class, ['label' => 'form.main_image', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ArticleImage::class, 'translation_domain' => 'messages']);
    }
}
