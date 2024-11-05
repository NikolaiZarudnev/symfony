<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecoveryPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['mode'] === 'email') {
            $builder
                ->add('email', EmailType::class, ['mapped' => false])
                ->add('save', SubmitType::class, ['label' => 'send email'])
                ->getForm();
        } else {
            $builder
                ->add('plainPassword', PasswordType::class, [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'label' => 'new password',
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
                ])
                ->add('save', SubmitType::class, ['label' => 'submit'])
                ->getForm();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
        $resolver->setRequired(array(
            'mode'
        ));
    }
}
