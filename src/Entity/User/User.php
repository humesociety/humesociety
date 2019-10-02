<?php

namespace App\Entity\User;

use App\Entity\Candidate\Candidate;
use App\Entity\Conference\Conference;
use App\Entity\Reviewer\Reviewer;
use App\Entity\Submission\Submission;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User objects represent users of the site with login credentials.
 *
 * Every user has the 'ROLE_USER' role. Members of the society also have the 'ROLE_MEMBER' role. The
 * `dues` field stores the date their next membership payment is due. Those for whom the dues date
 * is greater than today's date represent members in good standing. The members section of the web
 * site is restricted to members in good standing.
 *
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @UniqueEntity(
 *     fields={"username"},
 *     message="There is already an account with this username."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="There is already an account with this email address."
 * )
 */
class User implements UserInterface
{
    /**
     * The user's unique identifier in the database.
     *
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * The user's (unique) username.
     *
     * @var string
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("json")
     */
    private $username;

    /**
     * The user's (unique) email address.
     *
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups("json")
     * @Assert\Email()
     */
    private $email;

    /**
     * The user's security roles (used by Symfony's security component).
     *
     * @var string[]
     * @ORM\Column(type="json")
     */
    private $roles;

    /**
     * The user's encrypted password.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * The date the user was created.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date")
     * @Groups("json")
     */
    private $dateJoined;

    /**
     * Whether the user was rejoining when they created this account.
     *
     * Note there's no magic way of determining this; we simply have to ask users when they
     * register.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     * @Groups("json")
     */
    private $rejoined;

    /**
     * When the user last logged in.
     *
     * @var \DateTimeInterface
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastLogin;

    /**
     * A collection of the user's candidacies, i.e. times they have stood for election to the
     * committee.
     *
     * @var Candidate[]
     * @ORM\OneToMany(targetEntity="App\Entity\Candidate\Candidate", mappedBy="user")
     * @ORM\JoinColumn(nullable=false)
     */
    private $candidacies;

    /**
     * Whether the user has voted in the current election.
     *
     * This should be reset to false at the start of each election.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $voted;

    /**
     * Notes about this user (added by the EVPT or Technical Director).
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * The date this user's membership expires. Null for non-members.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     * @Groups("json")
     */
    private $dues;

    /**
     * Whether this user is a lifetime member of the society.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     * @Groups("json")
     */
    private $lifetimeMember;

    /**
     * This user's password reset secret (used to generate a link to reset the password).
     *
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $passwordResetSecret;

    /**
     * When this user's password reset secret expires.
     *
     * @var \DateTimeInterface|null
     * @ORM\Column(type="date", nullable=true)
     */
    private $passwordResetSecretExpires;

    /**
     * The user's first name.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $firstname;

    /**
     * The user's last name.
     *
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $lastname;

    /**
     * The user's department.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $department;

    /**
     * The user's institution.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $institution;

    /**
     * The user's city.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $city;

    /**
     * The user's state.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $state;

    /**
     * The three-letter country code of the user's country.
     *
     * @var string|null
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Groups("json")
     */
    private $country;

    /**
     * The user's office phone number.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $officePhone;

    /**
     * The user's home phone number.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $homePhone;

    /**
     * The user's fax number.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $fax;

    /**
     * The user's web page.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $webpage;

    /**
     * Whether the user wishes to receive general emails.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("json")
     */
    private $receiveEmail;

    /**
     * Whether the user wishes to receive a copy of Hume Studies in the post.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": true})
     * @Groups("json")
     */
    private $receiveHumeStudies;

    /**
     * The user's mailing address (for receiving Hume Studies in the post).
     *
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $mailingAddress;

    /**
     * A collection of the user's submissions to the Hume Conference.
     *
     * @var Submission[]
     * @ORM\OneToMany(targetEntity="App\Entity\Submission\Submission", mappedBy="user")
     * @ORM\JoinColumn(nullable=false)
     */
    private $submissions;

    /**
     * The user's linked reviewer entity (if any).
     *
     * @var Reviewer
     * @ORM\OneToOne(targetEntity="App\Entity\Reviewer\Reviewer", mappedBy="user")
     * @ORM\JoinColumn(nullable=true)
     */
    private $reviewer;

    /**
     * Whether the user is willing to receive requests to review articles.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $willingToReview;

    /**
     * Whether the user is willing to comment on a paper for the next Hume Conference.
     *
     * Note that this should automatically be reset to false when the conference ends.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $willingToComment;

    /**
     * Whether the user is willing to chair a session at the next Hume Conference.
     *
     * Note that this should automatically be reset to false when the conference ends.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $willingToChair;

    /**
     * A comma-separated list of keywords representing the user's areas of expertise.
     *
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $keywords;

    /**
     * Whether the user is a member of the society.
     *
     * @var bool
     * @Groups("json")
     */
    private $member;

    /**
     * Whether the user is a member in good standing.
     *
     * @var bool
     * @Groups("json")
     */
    private $memberInGoodStanding;

    /**
     * Whether the user is a member in arrears.
     *
     * @var bool
     * @Groups("json")
     */
    private $memberInArrears;

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct()
    {
        $this->roles = ['ROLE_USER']; // everyone is at least a user
        $this->country = 'USA'; // this is most likely, so will save most people some time
        $this->dateJoined = new \DateTime();
        $this->rejoined = false; // TODO: let people indicate if they are rejoining
        $this->candidacies = new ArrayCollection();
        $this->voted = false;
        $this->lifetimeMember = false;
        $this->receiveEmail = true;
        $this->receiveHumeStudies = true;
        $this->willingToReview = false;
        $this->willingToComment = false;
        $this->willingToChair = false;
        $this->submissions = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * Get the user's unique identifier in the database (null when the object is first created).
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the user's (unique) username (null when the object is first created).
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Set the user's (unique) username.
     *
     * @param string The user's (unique) username.
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the user's (unique) email address (null when the object is first created).
     *
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the user's (unique) email address.
     *
     * @param string The user's (unique) email address.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the user's security roles.
     *
     * Possible security roles are:
     *   - ROLE_USER: a regular user; everyone has at least this role
     *   - ROLE_MEMBER: a member of the society (not necessarily in good standing)
     *   - ROLE_TECH: the society's technical director
     *   - ROLE_EVPT: the society's executive vice-president treasurer
     *   - ROLE_ORGANISER: a conference organiser (for the current conference)
     *   - ROLE_EDITOR: an editor of Hume Studies
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Add a security role for this user.
     *
     * @param string The security role to add.
     * @return self
     */
    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $allowedRoles = [
                'ROLE_USER',
                'ROLE_MEMBER',
                'ROLE_TECH',
                'ROLE_EVPT',
                'ROLE_ORGANISER',
                'ROLE_EDITOR'
            ];
            if (in_array($role, $allowedRoles)) {
                $this->roles[] = $role;
            }
        }

        return $this;
    }

    /**
     * Remove a security role for this user.
     *
     * @param string The security role to remove.
     * @return self
     */
    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Get the user's encrypted password (null when the object is first created).
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the user's encrypted password.
     *
     * @var string The user's encrypted password.
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the date the user was created.
     *
     * @return \DateTimeInterface
     */
    public function getDateJoined(): \DateTimeInterface
    {
        return $this->dateJoined;
    }

    /**
     * Get whether the user was rejoining when they created this account.
     *
     * @return bool
     */
    public function getRejoined(): bool
    {
        return $this->rejoined;
    }

    /**
     * Set whether the user was rejoining when they created this account.
     *
     * @param bool Whether the user was rejoining when they created this account.
     * @return self
     */
    public function setRejoined(bool $rejoined): self
    {
        $this->rejoined = $rejoined;

        return $this;
    }

    /**
     * Get when the user last logged in (null until the first login).
     *
     * @return \DateTimeInterface|null
     */
    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    /**
     * Set when the user last logged in.
     *
     * @param \DateTimeInterface When the user last logged in.
     * @return self
     */
    public function setLastLogin(\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get the collection of the user's candidacies.
     *
     * @return Candidate[]
     */
    public function getCandidacies(): Collection
    {
        return $this->candidacies;
    }

    /**
     * Get whether the user has voted in the current election.
     *
     * @return bool
     */
    public function getVoted(): bool
    {
        return $this->voted;
    }

    /**
     * Set whether the user has voted in the current election.
     *
     * @param bool Whether the user has voted in the current election.
     * @return self
     */
    public function setVoted(bool $voted): self
    {
        $this->voted = $voted;

        return $this;
    }

    /**
     * Get notes about this user.
     *
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Set notes about this user.
     *
     * @param string|null Notes about this user.
     * @return self
     */
    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the date this user's membership expires.
     *
     * @return \DateTimeInterface|null
     */
    public function getDues(): ?\DateTimeInterface
    {
        return $this->dues;
    }

    /**
     * Set the date this user's membership expires.
     *
     * Membership expires at the end of June or the end of December.
     *
     * @param int How many years to add to this user's membership, starting from today's date.
     * @return self
     */
    public function setDues(int $yearsToAdd): self
    {
        $currentMonth = (int) date('m');
        $currentYear = date('Y');
        $lastYear = (string) ((int) $currentYear - 1);
        $this->dues = ($currentMonth > 6)
            ? new \DateTime($currentYear.'-06-30')
            : new \DateTime($currentYear.'-12-31');

        if ($yearsToAdd > 0) {
            $this->dues->add(new \DateInterval('P'.$yearsToAdd.'Y'));
        }

        return $this;
    }

    /**
     * Get whether this user is a lifetime member of the society.
     *
     * @return bool
     */
    public function getLifetimeMember(): bool
    {
        return $this->lifetimeMember;
    }

    /**
     * Set whether this user is a lifetime member of the society.
     *
     * @param bool Whether this user is a lifetime member of the society.
     * @return self
     */
    public function setLifetimeMember(bool $lifetimeMember): self
    {
        $this->lifetimeMember = $lifetimeMember;

        return $this;
    }

    /**
     * Get this user's password reset secret.
     *
     * @return string|null
     */
    public function getPasswordResetSecret(): ?string
    {
        return $this->passwordResetSecret;
    }

    /**
     * Randomly set this user's password reset secret (and when it expires).
     *
     * @return self
     */
    public function setPasswordResetSecret(): self
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->passwordResetSecret = '';
        for ($i = 0; $i < 10; $i++) {
            $this->passwordResetSecret .= $characters[rand(0, strlen($characters) - 1)];
        }
        $this->passwordResetSecretExpires = new \DateTime('+1 day');

        return $this;
    }

    /**
     * Get when this user's password reset secret expires.
     *
     * @return string|null
     */
    public function getPasswordResetSecretExpires(): ?\DateTimeInterface
    {
        return $this->passwordResetSecretExpires;
    }

    /**
     * Get the user's first name (null when the object is first created).
     *
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * Set the user's first name.
     *
     * @param string The user's first name.
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the user's last name (null when the object is first created).
     *
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * Set the user's last name.
     *
     * @param string The user's last name.
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the user's department.
     *
     * @return string|null
     */
    public function getDepartment(): ?string
    {
        return $this->department;
    }

    /**
     * Set the user's department.
     *
     * @param string|null The user's department.
     * @return self
     */
    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get the user's institution.
     *
     * @return string|null
     */
    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    /**
     * Set the user's institution.
     *
     * @param string|null The user's institution.
     * @return self
     */
    public function setInstitution(?string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get the user's city.
     *
     * @return string|null
     */
    public function getCity() : ?string
    {
        return $this->city;
    }

    /**
     * Set the user's city.
     *
     * @param string|null The user's city.
     * @return self
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the user's state.
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Set the user's state.
     *
     * @param string|null The user's state.
     * @return self
     */
    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get the three-letter country code of the user's country.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set the three-letter country code of the user's country.
     *
     * @param string|null The three-letter country code of the user's country.
     * @return self
     */
    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get the user's office phone number.
     *
     * @return string|null
     */
    public function getOfficePhone(): ?string
    {
        return $this->officePhone;
    }

    /**
     * Set the user's office phone number.
     *
     * @param string|null The user's office phone number.
     * @return self
     */
    public function setOfficePhone(?string $officePhone): self
    {
        $this->officePhone = $officePhone;

        return $this;
    }

    /**
     * Get the user's home phone number.
     *
     * @return string|null
     */
    public function getHomePhone(): ?string
    {
        return $this->homePhone;
    }

    /**
     * Set the user's home phone number.
     *
     * @param string|null The user's home phone number.
     * @return self
     */
    public function setHomePhone(?string $homePhone): self
    {
        $this->homePhone = $homePhone;

        return $this;
    }

    /**
     * Get the user's fax number.
     *
     * @return string|null
     */
    public function getFax(): ?string
    {
        return $this->fax;
    }

    /**
     * Set the user's fax number.
     *
     * @param string|null The user's fax number.
     * @return self
     */
    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get the user's web page.
     *
     * @return string|null
     */
    public function getWebpage(): ?string
    {
        return $this->webpage;
    }

    /**
     * Set the user's web page.
     *
     * @param string|null The user's web page.
     * @return self
     */
    public function setWebpage(?string $webpage): self
    {
        $this->webpage = $webpage;

        return $this;
    }

    /**
     * Get whether the user wishes to receive general emails.
     *
     * @return bool
     */
    public function getReceiveEmail(): ?bool
    {
        return $this->receiveEmail;
    }

    /**
     * Set whether the user wishes to receive general emails.
     *
     * @param bool Whether the user wishes to receive general emails.
     * @return self
     */
    public function setReceiveEmail(bool $receiveEmail): self
    {
        $this->receiveEmail = $receiveEmail;

        return $this;
    }

    /**
     * Get whether the user wishes to receive a copy of Hume Studies in the post.
     *
     * @return bool
     */
    public function getReceiveHumeStudies(): ?bool
    {
        return $this->receiveHumeStudies;
    }

    /**
     * Set whether the user wishes to receive a copy of Hume Studies in the post.
     *
     * @param bool Whether the user wishes to receive a copy of Hume Studies in the post.
     * @return self
     */
    public function setReceiveHumeStudies(bool $receiveHumeStudies): self
    {
        $this->receiveHumeStudies = $receiveHumeStudies;

        return $this;
    }

    /**
     * Get the user's mailing address.
     *
     * @return string|null
     */
    public function getMailingAddress(): ?string
    {
        return $this->mailingAddress;
    }

    /**
     * Set the user's mailing address.
     *
     * @param string|null The user's mailing address.
     * @return self
     */
    public function setMailingAddress(?string $mailingAddress): self
    {
        $this->mailingAddress = $mailingAddress;

        return $this;
    }

    /**
     * Get the collection of the user's submissions to the Hume Conference.
     *
     * @param Conference|null Optional conference to restrict to.
     * @return Submission[]
     */
    public function getSubmissions(?Conference $conference = null): Collection
    {
        if ($conference == null) {
            return $this->submissions;
        }
        $submissions = new ArrayCollection();
        foreach ($this->submissions as $submission) {
            if ($submission->getConference() == $conference) {
                $submissions->add($submission);
            }
        }
        return $submissions;
    }

    /**
     * Get a collection of the user's submissions excluding those to the given conference.
     *
     * @param Conference|null Optional conference to exclude.
     * @return Submission[]
     */
    public function getSubmissionsWithoutConference(?Conference $conference = null): Collection
    {
        if ($conference == null) {
            return $this->submissions;
        }
        $submissions = new ArrayCollection();
        foreach ($this->submissions as $submission) {
            if ($submission->getConference() != $conference) {
                $submissions->add($submission);
            }
        }
        return $submissions;
    }

    /**
     * Get the user's linked reviewer entity.
     *
     * @return Reviewer|null
     */
    public function getReviewer(): ?Reviewer
    {
        return $this->reviewer;
    }

    /**
     * Get whether the user is willing to receive requests to review articles.
     *
     * @return bool
     */
    public function getWillingToReview(): ?bool
    {
        return $this->willingToReview;
    }

    /**
     * Set whether the user is willing to receive requests to review articles.
     *
     * @param bool Whether the user is willing to receive requests to review articles.
     * @return self
     */
    public function setWillingToReview(bool $willingToReview): self
    {
        $this->willingToReview = $willingToReview;

        return $this;
    }

    /**
     * Get whether the user is willing to comment on a paper for the next Hume Conference.
     *
     * @return bool
     */
    public function getWillingToComment(): ?bool
    {
        return $this->willingToComment;
    }

    /**
     * Set whether the user is willing to comment on a paper for the next Hume Conference.
     *
     * @param bool Whether the user is willing to comment on a paper for the next Hume Conference.
     * @return self
     */
    public function setWillingToComment(bool $willingToComment): self
    {
        $this->willingToComment = $willingToComment;

        return $this;
    }

    /**
     * Get whether the user is willing to chair a session at the next Hume Conference.
     *
     * @return bool
     */
    public function getWillingToChair(): ?bool
    {
        return $this->willingToChair;
    }

    /**
     * Set whether the user is willing to chair a session at the next Hume Conference.
     *
     * @param bool Whether the user is willing to chair a session at the next Hume Conference.
     * @return self
     */
    public function setWillingToChair(bool $willingToChair): self
    {
        $this->willingToChair = $willingToChair;

        return $this;
    }

    /**
     * Get the comma-separated list of keywords representing the user's areas of expertise.
     *
     * @return string|null
     */
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    /**
     * Set the comma-separated list of keywords representing the user's areas of expertise.
     *
     * @param string|null The comma-separated list of keywords representing the user's areas of expertise.
     * @return self
     */
    public function setKeywords(?string $keywords): self
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get whether the user is a member of the society.
     *
     * @return bool
     */
    public function isMember(): bool
    {
        return in_array('ROLE_MEMBER', $this->roles);
    }

    /**
     * Get whether the user is a member in good standing.
     *
     * @return bool
     */
    public function isMemberInGoodStanding(): bool
    {
        return $this->isMember() && ($this->lifetimeMember || $this->dues > new \DateTime());
    }

    /**
     * Get whether the user is a member in arrears.
     *
     * @return bool
     */
    public function isMemberInArrears(): bool
    {
        return $this->isMember() && (!$this->lifetimeMember && $this->dues < new \DateTime());
    }

    /**
     * Get whether the user has submitted a paper for the given conference.
     *
     * @param Conference The conference to check.
     * @return bool
     */
    public function hasSubmittedToConference(Conference $conference)
    {
        foreach ($this->submissions as $submission) {
            if ($submission->getConference() == $conference) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get salt. This is needed by the Symfony security component.
     */
    public function getSalt()
    {
    }

    /**
     * Erase credentials. This is needed by the Symfony security component.
     */
    public function eraseCredentials()
    {
    }
}
