<?php

namespace App\Entity\Issue;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The journal issue form type.
 */
class IssueType extends AbstractType
{
    /**
     * Build the form.
     *
     * @param FormBuilderInterface $builder Symfony's form builder interface.
     * @param array An array of options.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('volume')
            ->add('number')
            ->add('museId', null, ['label' => 'Project MUSE ID'])
            ->add('name')
            ->add('editors');
    }

    /**
     * Configure the form's options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Issue::class]);
    }
}
