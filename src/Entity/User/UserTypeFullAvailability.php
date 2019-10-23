<?php

namespace App\Entity\User;

use App\Entity\Conference\ConferenceHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for setting a user's availability to review/comment/chair.
 *
 * This form type should only be used if there is a current conference in the database. Otherwise
 * the UserPartialAvailabilityType should be used instead.
 */
class UserTypeFullAvailability extends AbstractType
{
    /**
     * A string describing the current conference (null if there isn't one).
     *
     * @var string|null
     */
    private $conferenceInfo;

    /**
     * Constructor function.
     *
     * @throws \Exception
     * @throw \Error
     * @param ConferenceHandler $conferences The conference handler (dependency injection).
     */
    public function __construct(ConferenceHandler $conferences)
    {
        $currentConference = $conferences->getCurrentConference();
        // getCurrentConference may return null, in which case we shouldn't be here at all
        if ($currentConference === null) {
            throw new \Error('Full availability cannot be set when there is no current conference.');
        }
        $this->conferenceInfo = $currentConference->getOrdinal();
        $this->conferenceInfo .= ' Hume Conference in ';
        $this->conferenceInfo .= $currentConference->getTown();
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
            ->add('willingToReview', CheckboxType::class, [
                'label' => 'I am willing to review papers for Hume Studies or the Hume Conference',
                'required' => false
            ])
            ->add('willingToComment', CheckboxType::class, [
                'label' => 'I am willing to comment on a paper for the '.$this->conferenceInfo,
                'required' => false
            ])
            ->add('willingToChair', CheckboxType::class, [
                'label' => 'I am willing to chair a session at the '.$this->conferenceInfo,
                'required' => false
            ])
            ->add('keywords', TextType::class, [
                'label' => 'Areas of expertise (comma-separated)'
            ]);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
