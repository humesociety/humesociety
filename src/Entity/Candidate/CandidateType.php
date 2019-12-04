<?php

namespace App\Entity\Candidate;

use App\Entity\User\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
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
    * @param FormBuilderInterface $builder Symfony's form builder interface.
    * @param array $options An array of options.
    * @return void
    */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('user', null, [
                'required' => false,
                'placeholder' => '[none]',
                'label' => 'Linked User',
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
                        ->andWhere('u.dues > :now')
                        ->setParameter('now', new \DateTime('today'))
                        ->orderBy('u.lastname, u.firstname', 'ASC');
                },
                'attr' => [
                    'data-action' => 'fill-user-details',
                    'data-form' => $this->getBlockPrefix()
                ]
            ])
            ->add('institution')
            ->add('start')
            ->add('end')
            ->add('elected')
            ->add('reelectable')
            ->add('president')
            ->add('evpt', null, ['label' => 'EVPT', 'required' => false])
            ->add('votes')
            ->add('inRunOff')
            ->add('runOffVotes')
            ->add('description');
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Candidate::class]);
    }
}
