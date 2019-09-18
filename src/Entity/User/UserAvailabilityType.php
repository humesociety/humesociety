<?php

namespace App\Entity\User;

use App\Entity\Conference\ConferenceHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAvailabilityType extends AbstractType
{
    private $conferenceInfo;

    public function __construct(ConferenceHandler $conferenceHandler)
    {
        $nextConference = $conferenceHandler->getCurrentConference();
        $this->conferenceInfo = $nextConference->getOrdinal();
        $this->conferenceInfo .= ' Hume Conference in ';
        $this->conferenceInfo .= $nextConference->getTown();
    }

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
