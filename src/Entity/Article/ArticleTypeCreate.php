<?php

namespace App\Entity\Article;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for uploading an article. Like the one for editing an article, except that the
 * file field is required.
 */
class ArticleTypeCreate extends AbstractType
{
    /**
     * Build the form.
     *
     * @param FormBuilderInferface Symfony's form builder interface.
     * @param array An array of options.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('authors')
            ->add('startPage')
            ->add('endPage')
            ->add('museId', null, ['label' => 'Project MUSE ID', 'required' => false])
            ->add('doi', null, ['label' => 'DOI', 'required' => false])
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
            'data_class' => Article::class,
            'validation_groups' => ['Default', 'create']
        ]);
    }
}
