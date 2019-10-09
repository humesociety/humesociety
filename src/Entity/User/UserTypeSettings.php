<?php

namespace App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for editinf a user's membership preferences.
 */
class UserTypeSettings extends AbstractType
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
            ->add('receiveEmail', CheckboxType::class, [
                'label' => 'Receive Email',
                'required' => false
            ])
            ->add('receiveHumeStudies', CheckboxType::class, [
                'label' => 'Receive Hume Studies',
                'required' => false
            ])
            ->add('mailingAddress', TextareaType::class, [
                'label' => 'Mailing Address (for Hume Studies)',
                'required' => false
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
