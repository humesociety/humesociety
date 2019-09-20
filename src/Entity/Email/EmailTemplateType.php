<?php

namespace App\Entity\Email;

use App\Entity\User\UserHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The email template form type.
 */
class EmailTemplateType extends AbstractType
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
        $evpt = $userHandler->getVicePresident();
        $evptDisplay = $evpt
            ? $evpt.' <vicepresident@humesociety.org>'
            : 'Executive Vice-President Treasrer <vicepresident@humesociety.org>';
        $tech = $userHandler->getTechnicalDirector();
        $techDisplay = $tech
            ? $tech.' <web@humesociety.org>'
            : 'Technical Director <vicepresident@humesociety.org>';
        $organisers = $userHandler->getConferenceOrganisers();
        $organisersDisplay = (sizeof($organisers) > 0)
            ? implode(', ', $organisers).' <conference@humesociety.org>'
            : 'Conference Organisers <vicepresident@humesociety.org>';
        $this->senders = [
            $evptDisplay => 'vicepresident',
            $techDisplay => 'web',
            $organisersDisplay => 'conference'
        ];
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
            ->add('sender', ChoiceType::class, ['choices' => $this->senders])
            ->add('subject')
            ->add('content');
    }

    /**
     * Configure the form's options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => EmailTemplate::class]);
    }
}
