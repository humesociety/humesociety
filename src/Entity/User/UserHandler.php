<?php

namespace App\Entity\User;

use App\Entity\Conference\Conference;
use App\Entity\DuesPayment\DuesPayment;
use App\Entity\Invitation\Invitation;
use App\Entity\Submission\SubmissionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * The user handler contains the main business logic for reading and writing user data.
 */
class UserHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The user repository.
     *
     * @var EntityRepository
     */
    private $repository;

    /**
     * The submission handler.
     *
     * @var SubmissionHandler
     */
    private $submissions;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface $manager The Doctrine entity manager.
     * @param SubmissionHandler $submissions The submission handler.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, SubmissionHandler $submissions)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(User::class);
        $this->submissions = $submissions;
    }

    /**
     * Create an invited user from an invitation.
     *
     * @param Invitation $invitation The invitation.
     * @throws \Exception
     * @return User
     */
    public function createInvitedUser(Invitation $invitation): User
    {
        $user = new User();
        $user->setInvited(true);
        $user->setActive(true);
        $user->setFirstname($invitation->getFirstname());
        $user->setLastname($invitation->getLastname());
        $user->setEmail($invitation->getEmail());
        // username and password cannot be blank, so set some values; these won't do anything, however -
        // invited users cannot log in
        $user->setUsername($invitation->getEmail());
        $user->setPassword('password');
        return $user;
    }

    /**
     * Get an array of all users.
     *
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->repository->findBy([], ['lastname' => 'ASC', 'firstname' => 'ASC']);
    }

    /**
     * Get an array of all members.
     *
     * @return User[]
     */
    public function getMembers(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an array of all members in good standing.
     *
     * @throws \Exception
     * @return User[]
     */
    public function getMembersInGoodStanding(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.lifetimeMember = true OR u.dues >= :now')
            ->setParameter('now', new \DateTime('today'))
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an array of all members in arrears.
     *
     * @throws \Exception
     * @return User[]
     */
    public function getMembersInArrears(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.lifetimeMember = false AND u.dues < :now')
            ->setParameter('now', new \DateTime('today'))
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an array of all members whose membership expires (at the end of) this month.
     *
     * @throws \Exception
     * @return User[]
     */
    public function getMembersExpiringThisMonth(): array
    {
        $endOfThisMonth = new \DateTime(date('Y-m-t'));
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.lifetimeMember = false AND u.dues = :date')
            ->setParameter('date', $endOfThisMonth)
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get an array of all members who have elected to receive a copy of Hume Studies in the post.
     *
     * @throws \Exception
     * @return User[]
     */
    public function getMembersReceivingHumeStudies(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_MEMBER%\'')
            ->andWhere('u.receiveHumeStudies = true')
            ->andWhere('u.dues >= :now')
            ->setParameter('now', new \DateTime('today'))
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get current EVPT.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return User|null
     */
    public function getVicePresident(): ?User
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_EVPT%\'')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get current president.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return User|null
     */
    public function getPresident(): ?User
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_PRES%\'')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get current technical director.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return User|null
     */
    public function getTechnicalDirector(): ?User
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_TECH%\'')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get current conference organisers.
     *
     * @return User[]
     */
    public function getConferenceOrganisers(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_ORGANISER%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get current journal editors.
     *
     * @return User[]
     */
    public function getJournalEditors(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.roles LIKE \'%ROLE_EDITOR%\'')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users willing to review.
     *
     * @return User[]
     */
    public function getReviewVolunteers(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.willingToReview = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users willing to comment.
     *
     * @return User[]
     */
    public function getCommentVolunteers(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.willingToComment = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get users willing to review.
     *
     * @return User[]
     */
    public function getChairVolunteers(): array
    {
        return $this->repository->createQueryBuilder('u')
            ->where('u.willingToChair = TRUE')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get official society email addresses.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return object[]
     */
    public function getOfficialEmails(): array
    {
        $evpt = $this->getVicePresident();
        $evptDisplay = $evpt
            ? $evpt->getFullname().' <vicepresident@humesociety.org>'
            : 'Executive Vice-President Treasurer <vicepresident@humesociety.org>';
        $pres = $this->getPresident();
        $presDisplay = $pres
            ? $pres->getFullname().' <president@humesociety.org>'
            : 'President <vicepresident@humesociety.org>';
        $tech = $this->getTechnicalDirector();
        $techDisplay = $tech
            ? $tech->getFullname().' <web@humesociety.org>'
            : 'Technical Director <vicepresident@humesociety.org>';
        /*
        N.B. will have to fix this properly next year; after we agreed to do it this way, Saul changed
        his mind, so I'm just hard coding it for now so as not to complicate things while the review
        process is underway
        $organisers = $this->getConferenceOrganisers();
        $organisersDisplay = (sizeof($organisers) > 0)
            ? implode(', ', array_map(function ($organiser) {
                return $organiser->getFullname();
            }, $organisers)).' <conference@humesociety.org>'
            : 'Conference Organisers <vicepresident@humesociety.org>';
        */
        $organisersDisplay = 'Ann Levey, Saul Traiger <conference@humesociety.org>';
        return [
            $evptDisplay => 'vicepresident',
            $presDisplay => 'president',
            $techDisplay => 'web',
            $organisersDisplay => 'conference'
        ];
    }

    /**
     * Get one user by their username.
     *
     * @param string $username The username of the user to look for.
     * @return User|null
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->repository->findOneByUsername($username);
    }

    /**
     * Get one user by their email.
     *
     * @param string $email The email of the user to look for.
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->repository->findOneByEmail($email);
    }

    /**
     * Get all reviewers for a given conference.
     *
     * @param Conference $conference The conference.
     * @return User[]
     */
    public function getReviewers(Conference $conference): array
    {
        return array_filter($this->getUsers(), function ($user) use ($conference) {
            return sizeof($user->getReviews($conference)) > 0;
        });
    }

    /**
     * Get all commentators for a given conference.
     *
     * @param Conference $conference The conference.
     * @return User[]
     */
    public function getCommentators(Conference $conference): array
    {
        return array_filter($this->getUsers(), function ($user) use ($conference) {
            return sizeof($user->getComments($conference)) > 0;
        });
    }

    /**
     * Get all chairs for a given conference.
     *
     * @param Conference $conference The conference.
     * @return User[]
     */
    public function getChairs(Conference $conference): array
    {
        return array_filter($this->getUsers(), function ($user) use ($conference) {
            return sizeof($user->getChairs($conference)) > 0;
        });
    }

    /**
     * Get all invited speakers for a given conference.
     *
     * @param Conference $conference The conference.
     * @return User[]
     */
    public function getSpeakers(Conference $conference): array
    {
        return array_filter($this->getUsers(), function ($user) use ($conference) {
            return sizeof($user->getPapers($conference)) > 0;
        });
    }

    /**
     * Save/update a user in the database.
     *
     * @param User $user The user to save/update.
     * @return void
     */
    public function saveUser(User $user)
    {
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * Refresh user.
     *
     * @param User $user The user to refresh.
     * @return void
     */
    public function refreshUser(User $user)
    {
        $this->manager->refresh($user);
    }

    /**
     * Delete a user from the database.
     *
     * @param User $user The user to delete.
     * @return void
     */
    public function deleteUser(User $user)
    {
        foreach ($user->getCandidacies() as $candidate) {
            // keep a record of their candidacy (for the society's records), but remove the explicit
            // user association
            $candidate->setUser(null);
            $this->manager->persist($candidate);
        }
        foreach ($user->getSubmissions() as $submission) {
            // delete the user's submissions
            $this->submissions->deleteSubmission($submission);
        }
        // TODO: if we try to delete a user who has done something for a conference,
        // this won't work - we'll have to delete or nullify those connections somehow
        $this->manager->remove($user);
        $this->manager->flush();
    }

    /**
     * Update a user's last login to today's date.
     *
     * @param User $user The user to be updated.
     * @throws \Exception
     * @return void
     */
    public function updateLastLogin(User $user)
    {
        $user->setLastLogin(new \DateTime('today'));
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * Update a user's dues payment date.
     *
     * @param User $user The user to be updated.
     * @param DuesPayment $duesPayment The payment record.
     * @throws \Exception
     * @return void
     */
    public function updateDues(User $user, DuesPayment $duesPayment)
    {
        switch ($duesPayment->getDescription()) {
            case 'Regular Membership (1 year)': // fallthrough
            case 'Student Membership (1 year)':
                $user->incrementDues(1);
                break;

            case 'Regular Membership (2 years)': // fallthrough
            case 'Student Membership (2 years)':
                $user->incrementDues(2);
                break;

            case 'Regular Membership (5 years)':
                $user->incrementDues(5);
                break;
        }
        $user->addRole('ROLE_MEMBER');
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * Reset voting records for the next election.
     */
    public function resetVotingRecords()
    {
        foreach ($this->getUsers() as $user) {
            if ($user->hasVoted()) {
                $user->setVoted(false);
                $this->saveUser($user);
            }
        }
    }
}
