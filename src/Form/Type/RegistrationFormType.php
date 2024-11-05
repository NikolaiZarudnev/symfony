<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'email',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form_control'
                ],
                'row_attr' => [
                    'class' => 'mb-3',
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'agree terms',
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form_control'
                ],
                'row_attr' => [
                    'class' => 'mb-3',
                ],
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'forms.constraints.agreeTerms',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label_attr' => [
                    'class' => 'form-label',
                ],
                'attr' => [
                    'class' => 'form_control'
                ],
                'row_attr' => [
                    'class' => 'mb-3',
                ],
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'forms.constraints.password.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'forms.constraints.password.length',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
