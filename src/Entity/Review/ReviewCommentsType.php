<?php

namespace App\Entity\Review;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for editing review comments as shown to the author.
 */
class ReviewCommentsType extends AbstractType
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
            ->add('commentsForAuthor', TextareaType::class);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Review::class]);
    }
}
