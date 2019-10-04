<?php

namespace App\Entity\Email;

use App\Entity\User\UserHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


/**
 * The email form type.
 */
class EmailType extends AbstractType
{
    /**
     * An associative array of senders.
     *
     * @var array
     */
    private $senders;

    /**
     * Constructor function.
     *
     * @param UserHandler
     * @return void
     */
    public function __construct(UserHandler $userHandler)
    {
        $this->senders = $userHandler->getOfficialEmails();
    }

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
                'label' => 'Members in Good Standing',
                'required' => false
            ])
            ->add('lapsed', CheckboxType::class, [
                'label' => 'Members in Arrears',
                'required' => false
            ])
            ->add('declining', CheckboxType::class, [
                'label' => 'Include Members Declining General Emails',
                'required' => false
            ])
            ->add('sender', ChoiceType::class, [
                'choices' => $this->senders,
                'label' => 'From'
            ])
            ->add('subject')
            ->add('attachment', FileType::class, ['required' => false])
            ->add('content', TextareaType::class);
    }
}
