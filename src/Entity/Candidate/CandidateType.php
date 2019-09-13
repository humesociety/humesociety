<?php

namespace App\Entity\Candidate;

use App\Entity\User\User;
use App\Entity\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('user', EntityType::class, [
                'required' => false,
                'placeholder' => '[none]',
                'label' => 'Linked User',
                'class' => User::class,
                'query_builder' => function (UserRepository $userRepository) {
                    return $userRepository->findElectableMembers();
                }
            ])
            ->add('institution')
            ->add('start')
            ->add('end')
            ->add('elected')
            ->add('reelectable')
            ->add('president')
            ->add('evpt', CheckboxType::class, ['label' => 'EVPT'])
            ->add('votes')
            ->add('description');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
