<?php

namespace App\Entity\Invitation;

use App\Entity\User\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for an invitation to an existing user.
 */
class InvitationTypeExisting extends AbstractType
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
        $builder->add('user', EntityType::class, [
            'class' => User::class,
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('u')
                    ->where('u.active = TRUE')
                    ->orderBy('u.lastname', 'ASC')
                    ->addOrderBy('u.firstname', 'ASC');
            }
        ]);
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => Invitation::class]);
    }
}
