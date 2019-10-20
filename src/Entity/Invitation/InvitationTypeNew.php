<?php

namespace App\Entity\Invitation;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for an invitation to a new user.
 */
class InvitationTypeNew extends AbstractType
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
            ->add('firstname', TextType::class, ['attr' => ['placeholder' => 'firstname']])
            ->add('lastname', TextType::class, ['attr' => ['placeholder' => 'lastname']])
            ->add('email', EmailType::class, ['attr' => ['placeholder' => 'email address']]);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Invitation::class]);
    }
}
