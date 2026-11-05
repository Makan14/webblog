<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire de création du compte administrateur unique.
 */
final class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'form.name',
                'constraints' => [new Assert\NotBlank(message: 'validation.not_blank'), new Assert\Length(min: 2, max: 100)],
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'constraints' => [new Assert\NotBlank(message: 'validation.not_blank'), new Assert\Email(message: 'validation.email')],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirm'],
                'invalid_message' => 'validation.password_mismatch',
                'constraints' => [
                    new Assert\NotBlank(message: 'validation.not_blank'),
                    new Assert\Length(min: 10, max: 4096, minMessage: 'validation.password_length'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'messages']);
    }
}
