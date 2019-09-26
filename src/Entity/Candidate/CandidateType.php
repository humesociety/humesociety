<?php

namespace App\Entity\Candidate;

use App\Entity\User\User;
use App\Entity\User\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for a candidate.
 */
class CandidateType extends AbstractType
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
            ->add('evpt', CheckboxType::class, ['label' => 'EVPT', 'required' => false])
            ->add('votes')
            ->add('description');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
