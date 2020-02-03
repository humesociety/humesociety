<?php

namespace App\Entity\Email;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * The individual email form type.
 */
class EmailTypeIndividual extends AbstractType
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
        $this->recipients = [];
        foreach ($users->getUsers() as $user) {
            $this->recipients[(string) $user] = $user->getEmail();
        }
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
            ->add('recipient', EntityType::class, [
                'label' => 'To',
                'class' => User::class,
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastname, u.firstname', 'ASC');
                }
            ])
            ->add('subject')
            ->add('attachment', FileType::class, ['required' => false])
            ->add('content', TextareaType::class);
    }
}
