<?php

namespace App\Entity\Conference;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for setting the basic properties of a conference.
 */
class ConferenceType extends AbstractType
{
    /**
     * An associative array of countries and their country codes (from the app's parameters).
     *
     * @var array
     */
    private $countries;

    /**
     * Constructor function.
     *
     * @param ParameterBagInterface $params Symfony's parameter bag interface.
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
            ->add('number')
            ->add('year')
            ->add('startDate', null, ['widget' => 'single_text', 'required' => false])
            ->add('endDate', null, ['widget' => 'single_text', 'required' => false])
            ->add('institution')
            ->add('town')
            ->add('country', ChoiceType::class, ['choices' => $this->countries])
            ->add('website');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Conference::class]);
    }
}
