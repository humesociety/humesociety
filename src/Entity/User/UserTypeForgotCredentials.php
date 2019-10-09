<?php

namespace App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The form type for requesting a password reset email.
 */
class UserTypeForgotCredentials extends AbstractType
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
            ->add('email', EmailType::class);
    }
}
