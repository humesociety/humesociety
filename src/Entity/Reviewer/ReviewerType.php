<?php

namespace App\Entity\Reviewer;

use App\Entity\User\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for a reviewer.
 */
class ReviewerType extends AbstractType
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
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Linked User',
                'required' => false,
                'placeholder' => '[none]',
                'attr' => [
                    'data-action' => 'fill-user-details',
                    'data-form' => $this->getBlockPrefix()
                ]
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('email')
            ->add('keywords');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Reviewer::class]);
    }
}
