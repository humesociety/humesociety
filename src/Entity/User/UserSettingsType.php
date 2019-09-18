<?php

namespace App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('receiveEmail', CheckboxType::class, [
                'label' => 'Receive Email',
                'required' => false
            ])
            ->add('receiveHumeStudies', CheckboxType::class, [
                'label' => 'Receive Hume Studies',
                'required' => false
            ])
            ->add('mailingAddress', TextareaType::class, [
                'label' => 'Mailing Address (for Hume Studies)',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
