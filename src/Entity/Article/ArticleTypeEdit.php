<?php

namespace App\Entity\Article;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for editing an article. Like the one for uploading an article, except that the
 * file field is optional.
 */
class ArticleTypeEdit extends AbstractType
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
            ->add('authors')
            ->add('startPage')
            ->add('endPage')
            ->add('museId', null, ['label' => 'Project MUSE ID', 'required' => false])
            ->add('doi', null, ['label' => 'DOI', 'required' => false])
            ->add('file', null, ['required' => false]);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Article::class]);
    }
}
