<?php

namespace App\Entity\Submission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for uploading the final version of a submission to the Hume Conference.
 */
class SubmissionTypeFinal extends AbstractType
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
            ->add('finalFile');
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
            'data_class' => Submission::class,
            'validation_groups' => ['Default', 'final']
        ]);
    }
}
