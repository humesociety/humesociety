<?php

namespace App\Entity\Paper;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for a paper for the Hume Conference.
 */
class PaperType extends AbstractType
{
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
            ->add('title')
            ->add('abstract')
            ->add('file');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Paper::class,
            'validation_groups' => ['Default', 'create']
        ]);
    }
}
