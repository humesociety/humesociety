<?php

namespace App\Entity\Submission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for a submission to the Hume Conference.
 */
class SubmissionType extends AbstractType
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
            ->add('title')
            ->add('authors')
            ->add('abstract')
            ->add('keywords')
            ->add('file');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Submission::class,
            'validation_groups' => ['Default', 'create', 'submission']
        ]);
    }
}
