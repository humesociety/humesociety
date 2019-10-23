<?php

namespace App\Entity\User;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for editing a user's details.
 */
class UserTypeDetails extends AbstractType
{
    /**
     * An associative array of countries and their country codes.
     *
     * @var array
     */
    private $countries;

    /**
     * Constructor function.
     *
     * @param ParameterBagInterface $params Symfony's parameter bag interface.
     * @return void
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->countries = [];
        foreach ($params->get('countries') as $id => $country) {
            $this->countries[$country] = $id;
        }
    }

    /**
     * Build the form.
     *
     * @param FormBuilderInterface $builder Symfony's form builder interface.
     * @param array $options An array of options.
     * @return void
     */
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

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
