<?php

namespace App\Form\Type;


use App\Entity\Address;
use App\Entity\City;
use App\Entity\Country;
use App\Form\DataTransformer\StreetTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    public function __construct(
        private StreetTransformer $transformer,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'required' => true,
                'label' => 'street.street',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                ],

            ])
            ->add('country', EntityType::class, [
                'class' => Country::class,
                'required' => true,
                'choice_label' => 'name',
                'label' => 'country',
            ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'required' => true,
                'choice_label' => 'name',
                'label' => 'city',
            ])
            ->add('zip', IntegerType::class, [
                'required' => true,
                'label' => 'zip',
            ]);

        $builder->get('street')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'validation_groups' => ['address'],
        ]);
    }
}