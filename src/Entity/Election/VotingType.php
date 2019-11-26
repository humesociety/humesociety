<?php

namespace App\Entity\Election;

use App\Entity\Candidate\Candidate;
use App\Entity\Candidate\CandidateHandler;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for voting in an election.
 */
class VotingType extends AbstractType
{
    /**
     * Array of candidates for the current election.
     *
     * @var Candidate[]
     */
    private $candidates;

    /**
     * Constructor function.
     *
     * @param CandidateHandler $candidates
     * @param ElectionHandler $elections
     * @throws NonUniqueResultException
     */
    public function __construct(CandidateHandler $candidates, ElectionHandler $elections)
    {
        $election = $elections->getOpenElection();
        if ($election) {
            // if there isn't an open election, this form type should never be instantiated
            $this->candidates = $candidates->getCandidatesByYear($election->getYear());
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
        foreach ($this->candidates as $candidate) {
            $label = "{$candidate->getFirstname()} {$candidate->getLastname()}, {$candidate->getInstitution()}";
            if ($candidate->getPresident()) {
                $builder->add($candidate->getId(), CheckboxType::class, [
                    'label' => $label.' (President)',
                    'required' => false,
                    'attr' => ['data-president' => 'true']
                ]);
            } elseif ($candidate->getEvpt()) {
              $builder->add($candidate->getId(), CheckboxType::class, [
                  'label' => $label.' (Executive Vice-President Treasurer)',
                  'required' => false,
                  'attr' => ['data-evpt' => 'true']
              ]);
            } else {
              $builder->add($candidate->getId(), CheckboxType::class, [
                  'label' => $label,
                  'required' => false,
                  'attr' => ['data-ordinary' => 'true']
              ]);
            }
        }
    }

    /**
     * Configure the form options.
     *
     * @param OptionsResolver $resolver Symfony's options resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
