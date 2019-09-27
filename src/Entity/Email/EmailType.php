<?php

namespace App\Entity\Email;

use App\Entity\User\UserHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            ->add('current', null, [
                'label' => 'Members in Good Standing',
                'required' => false
            ])
            ->add('lapsed', null, [
                'label' => 'Members in Arrears',
                'required' => false
            ])
            ->add('declining', null, [
                'label' => 'Include Members Declining General Emails',
                'required' => false
            ])
            ->add('sender', ChoiceType::class, [
                'choices' => $this->senders,
                'label' => 'From'
            ])
            ->add('subject')
            ->add('attachment', null, ['required' => false])
            ->add('content');
    }
}
