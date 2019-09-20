<?php

namespace App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for setting a user's availability to review.
 *
 * This form type should only be used if there is no current conference in the database. Otherwise
 * the UserFullAvailabilityType should be used instead.
 */
class UserPartialAvailabilityType extends AbstractType
{
    /**
     * Build the form.
     *
     * @param FormBuilderInterface Symfony's form builder interface.
     * @param array An array of options.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('willingToReview', CheckboxType::class, [
                'label' => 'I am willing to review papers for Hume Studies or the Hume Conference',
                'required' => false
            ])
            ->add('keywords', TextType::class, [
                'label' => 'Areas of expertise (comma-separated)'
            ]);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
