<?php

namespace App\Entity\User;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class UserDetailsType extends AbstractType
{
    private $countries;

    public function __construct(ParameterBagInterface $params)
    {
        $this->countries = [];
        foreach ($params->get('countries') as $id => $country) {
            $this->countries[$country] = $id;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('firstname')
            ->add('lastname')
            ->add('department')
            ->add('institution')
            ->add('city')
            ->add('state')
            ->add('country', ChoiceType::class, ['choices' => $this->countries])
            ->add('officePhone')
            ->add('homePhone')
            ->add('fax')
            ->add('webpage');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
