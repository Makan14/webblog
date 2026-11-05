<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Création et modification d'un article : texte, joueur et images.
 */
final class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'form.article_title'])
            ->add('joueur', ChoiceType::class, [
                'label' => 'form.player',
                'choices' => array_flip(Article::JOUEURS),
                'choice_label' => fn (string $libelle) => $libelle,
            ])
            ->add('extrait', TextareaType::class, [
                'label' => 'form.excerpt',
                'attr' => ['rows' => 3],
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'form.content',
                'attr' => ['rows' => 12],
            ])
            ->add('images', FileType::class, [
                'label' => 'form.images',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    new Assert\All([
                        new Assert\Image(
                            maxSize: '5M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                            mimeTypesMessage: 'validation.image_type',
                            maxSizeMessage: 'validation.image_size',
                        ),
                    ]),
                ],
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp', 'multiple' => true],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'translation_domain' => 'messages',
        ]);
    }
}
