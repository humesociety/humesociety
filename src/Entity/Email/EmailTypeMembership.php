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
 * The membership email form type.
 */
class EmailTypeMembership extends AbstractType
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
     * @param UserHandler $users The user handler.
     * @return void
     */
    public function __construct(UserHandler $users)
    {
        $this->senders = $users->getOfficialEmails();
    }

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
            ->add('sender', ChoiceType::class, [
                'choices' => $this->senders,
                'label' => 'From'
            ])
            ->add('current', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Members in Good Standing',
                'required' => false
            ])
            ->add('lapsed', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Members in Arrears',
                'required' => false
            ])
            ->add('declining', CheckboxType::class, [
                'mapped' => false,
                'label' => 'Include Members Declining General Emails',
                'required' => false
            ])
            ->add('subject')
            ->add('attachment', FileType::class, ['required' => false])
            ->add('content', TextareaType::class);
    }
}
