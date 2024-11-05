<?php

namespace App\Form\Type;

use App\Form\Objects\SearchObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchObjectType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => false,
                'label' => 'first name',
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
                'label' => 'last name',
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'email',
            ])
            ->add('address', TextareaType::class, [
                'required' => false,
                'label' => 'address',
            ])
            ->add('country', TextType::class, [
                'required' => false,
                'label' => 'country',
            ])
            ->add('search', SubmitType::class, [
                'label' => 'search'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchObject::class,
        ]);
    }
}