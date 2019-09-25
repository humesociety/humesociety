<?php

namespace App\Entity\Article;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for creating (uploading) an article.
 */
class ArticleCreateType extends AbstractType
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
            ->add('museId', IntegerType::class, ['label' => 'Project MUSE ID', 'required' => false])
            ->add('doi', TextType::class, ['label' => 'DOI', 'required' => false])
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
            'validation_groups' => ['Default', 'create', 'article']
        ]);
    }
}
