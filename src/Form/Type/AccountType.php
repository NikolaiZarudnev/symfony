<?php

namespace App\Form\Type;


use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isRequire = false;
        if ($options['mode'] === 'edit') {
            $isRequire = true;
        }
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'first name',
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'last name',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'email',
            ])
            ->add('companyName', TextType::class, [
                'required' => $isRequire,
                'label' => 'company name',
            ])
            ->add('position', TextType::class, [
                'required' => $isRequire,
                'label' => 'position',
            ])
            ->add('sex', ChoiceType::class, [
                'required' => $isRequire,
                'label' => 'sex',
                'choices' => [
                    'male' => Account::ACCOUNT_SEX_MALE,
                    'female' => Account::ACCOUNT_SEX_FEMALE,
                    'unknown' => Account::ACCOUNT_SEX_NOT_FOUND,
                ],
            ]);

        if ($options['mode'] === 'edit') {
            $builder
                ->add('address', AddressType::class);
        }

        $builder
            ->add('phones', CollectionType::class, [
                'entry_type' => PhoneType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'by_reference' => false,
            ])
            ->add('image', FileType::class, [
                'required' => false,
                'label' => 'image',
                'mapped' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'submit']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
            'validation_groups' => 'account',
        ]);
        $resolver->setRequired(array(
            'mode'
        ));
    }
}