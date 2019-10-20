<?php

namespace App\Entity\User;

use App\Entity\Conference\Conference;
use App\Entity\DuesPayment\DuesPayment;
use App\Entity\Invitation\Invitation;
use App\Entity\Submission\SubmissionHandler;
use Doctrine\ORM\EntityManagerInterface;

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
     * @var UserRepository
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
     * @param EntityManagerInterface The Doctrine entity manager.
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
     * @param Invitation The invitation.
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
        // the user will have to choose their own username and password if they accept the invitation
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
     * Get current technical director.
     *
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
     * Get an official society email address (address => name).
     *
     * @param string The type of email to get.
     * @return object
     */
    public function getOfficialEmail(string $sender): array
    {
        switch ($sender) {
            case 'vicepresident':
                $evpt = $this->getVicePresident();
                $name = $evpt ? $evpt->getFullname() : 'Executive Vice-President Treasurer';
                return ['vicepresident@humesociety.org' => $name];

            case 'conference':
                $organisers = $this->getConferenceOrganisers();
                $name = 'Conference Organisers';
                if (sizeof($organisers) > 0) {
                    $name = implode(', ', array_map(function ($organiser) {
                        return $organiser->getFullname();
                    }, $organisers));
                }
                return ['conference@humesociety.org' => $name];

            case 'web': // fallthrough
            default: // also make this the default, to ensure this function always returns something
                $tech = $this->getTechnicalDirector();
                $name = $tech ? $tech->getFullname() : 'Technical Director';
                return ['web@humesociety.org' => $name];
        }
    }

    /**
     * Get official society email addresses.
     *
     * @param string The type of email to get.
     * @return object[]
     */
    public function getOfficialEmails(): array
    {
        $evpt = $this->getVicePresident();
        $evptDisplay = $evpt
            ? $evpt->getFullname().' <vicepresident@humesociety.org>'
            : 'Executive Vice-President Treasrer <vicepresident@humesociety.org>';
        $tech = $this->getTechnicalDirector();
        $techDisplay = $tech
            ? $tech->getFullname().' <web@humesociety.org>'
            : 'Technical Director <vicepresident@humesociety.org>';
        $organisers = $this->getConferenceOrganisers();
        $organisersDisplay = (sizeof($organisers) > 0)
            ? implode(', ', array_map(function ($organiser) {
                return $organiser->getFullname();
            }, $organisers)).' <conference@humesociety.org>'
            : 'Conference Organisers <vicepresident@humesociety.org>';
        return [
            $evptDisplay => 'vicepresident',
            $techDisplay => 'web',
            $organisersDisplay => 'conference'
        ];
    }

    /**
     * Get one user by their username.
     *
     * @return User|null
     */
    public function getUserByUsername($username): ?User
    {
        return $this->repository->findOneByUsername($username);
    }

    /**
     * Get one user by their email.
     *
     * @return User|null
     */
    public function getUserByEmail($email): ?User
    {
        return $this->repository->findOneByEmail($email);
    }

    /**
     * Get all reviewers for a given conference.
     *
     * @param Conference The conference.
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
     * @param Conference The conference.
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
     * @param Conference The conference.
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
     * @param Conference The conference.
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
     * @param User The user to save/update.
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
     * @param User The user to refresh.
     * @return void
     */
    public function refreshUser(User $user)
    {
        $this->manager->refresh($user);
    }

    /**
     * Delete a user from the database.
     *
     * @param User The user to delete.
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
        if ($user->getReviewer()) {
            // keep the reviewer (to preserve their reviews), but remove the explicit user association
            $user->getReviewer()->setUser(null);
            $this->manager->persist($user->getReviewer());
        }
        $this->manager->remove($user);
        $this->manager->flush();
    }

    /**
     * Update a user's last login to today's date.
     *
     * @param User The user to be updated.
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
     * @param User The user to be updated.
     * @param DuesPayment The payment record.
     */
    public function updateDues(User $user, DuesPayment $duesPayment)
    {
        switch ($duesPayment->getDescription()) {
            case 'Regular Membership (1 year)': // fallthrough
            case 'Student Membership (1 year)':
                $user->setDues(1);
                break;
            case 'Regular Membership (2 years)': // fallthrough
            case 'Student Membership (2 years)':
                $user->setDues(2);
                break;
            case 'Regular Membership (5 years)':
                $user->setDues(5);
                break;
        }
        $user->addRole('ROLE_MEMBER');
        $this->manager->persist($user);
        $this->manager->flush();
    }
}
