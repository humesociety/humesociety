<?php

namespace App\Entity\Text;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for text variables.
 */
class TextType extends AbstractType
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
        $builder->add('content');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Text::class]);
    }
}
