<?php

namespace App\Entity\Email;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * The email form type.
 */
class EmailType extends AbstractType
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
            ->add('current', CheckboxType::class, [
                'label' => 'Send to Members in Good Standing',
                'required' => false
            ])
            ->add('lapsed', CheckboxType::class, [
                'label' => 'Send to Members in Arrears',
                'required' => false
            ])
            ->add('declining', CheckboxType::class, [
                'label' => 'Include Members Declining General Emails',
                'required' => false
            ])
            ->add('subject', TextType::class)
            ->add('attachment', FileType::class, [
                'required' => false
            ])
            ->add('content', TextareaType::class);
    }
}
