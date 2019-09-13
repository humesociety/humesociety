<?php

namespace App\Entity\User;

use App\Entity\Candidate\Candidate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @UniqueEntity(
 *     fields={"username"},
 *     message="There is already an account with this username."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="There is already an account with this email address."
 * )
 *
 * User objects represent users of the site with login credentials. Every user has the 'ROLE_USER'
 * role. Members of the society also have the 'ROLE_MEMBER' role. The `dues` field stores the date
 * their next membership payment is due. Those for whom the dues date is less than today's date
 * represent members in good standing. The members section of the web site is restricted to members
 * in good standing.
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("json")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups("json")
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="date")
     * @Groups("json")
     */
    private $dateJoined;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("json")
     */
    private $rejoined;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $voted;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Candidate\Candidate", mappedBy="user")
     */
    private $offices;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("json")
     */
    private $dues;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("json")
     */
    private $lifetimeMember;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $passwordResetSecret;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $passwordResetSecretExpires;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("json")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $institution;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Groups("json")
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $officePhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $homePhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $fax;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("json")
     */
    private $webpage;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("json")
     */
    private $receiveEmail;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("json")
     */
    private $receiveHumeStudies;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $mailingAddress;

    /**
     * derived property; getter defined below
     * @Groups("json")
     */
    private $member;

    /**
     * derived property; getter defined below
     * @Groups("json")
     */
    private $memberInGoodStanding;

    /**
     * derived property; getter defined below
     * @Groups("json")
     */
    private $memberInArrears;

    // Constructor function
    public function __construct()
    {
        $this->roles = ['ROLE_USER']; // everyone is at least a user
        $this->country = 'USA'; // this is most likely, so will save most people some time
        $this->dateJoined = new \DateTime();
        $this->rejoined = false; // TODO: let people indicate if they are rejoining
        $this->voted = false;
        $this->offices = new ArrayCollection();
        $this->lifetimeMember = false;
        $this->receiveEmail = true;
        $this->receiveHumeStudies = true;
    }

    // ToString function, for displaying in dropdown menu in forms
    public function __toString(): string
    {
        return $this->lastname.', '.$this->firstname.' ['.$this->username.']';
    }

    // Getters and setters for private properties
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDateJoined(): ?\DateTimeInterface
    {
        return $this->dateJoined;
    }

    public function setDateJoined(\DateTimeInterface $dateJoined): self
    {
        $this->dateJoined = $dateJoined;

        return $this;
    }

    public function getRejoined(): ?bool
    {
        return $this->rejoined;
    }

    public function setRejoined(bool $rejoined): self
    {
        $this->rejoined = $rejoined;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getVoted(): ?bool
    {
        return $this->voted;
    }

    public function setVoted(bool $voted): self
    {
        $this->voted = $voted;

        return $this;
    }

    public function getOffices(): Collection
    {
        return $this->offices;
    }

    public function addOffice(Candidate $office): self
    {
        if (!$this->offices->contains($office)) {
            $this->offices[] = $office;
            $office->setUser($this);
        }

        return $this;
    }

    public function removeOffice(Candidate $office): self
    {
        if ($this->offices->contains($office)) {
            $this->offices->removeElement($office);
            if ($office->getUser() === $this) {
                $office->setUser(null);
            }
        }

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getDues(): ?\DateTimeInterface
    {
        return $this->dues;
    }

    public function setDues(\DateTimeInterface $dues): self
    {
        $this->dues = $dues;

        return $this;
    }

    public function getLifetimeMember(): ?bool
    {
        return $this->lifetimeMember;
    }

    public function setLifetimeMember(bool $lifetimeMember): self
    {
        $this->lifetimeMember = $lifetimeMember;

        return $this;
    }

    public function getPasswordResetSecret(): ?string
    {
        return $this->passwordResetSecret;
    }

    public function setPasswordResetSecret(string $passwordResetSecret): self
    {
        $this->passwordResetSecret = $passwordResetSecret;

        return $this;
    }

    public function setRandomPasswordResetSecret(): self
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->passwordResetSecret = '';
        for ($i = 0; $i < 10; $i++) {
            $this->passwordResetSecret .= $characters[rand(0, strlen($characters) - 1)];
        }
        $this->passwordResetSecretExpires = new \DateTime('+1 day');

        return $this;
    }

    public function getPasswordResetSecretExpires(): ?\DateTimeInterface
    {
        return $this->passwordResetSecretExpires;
    }

    public function setPasswordResetSecretExpires(\DateTimeInterface $passwordResetSecretExpires): self
    {
        $this->passwordResetSecretExpires = $passwordResetSecretExpires;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getInstitution(): ?string
    {
        return $this->institution;
    }

    public function setInstitution(?string $institution): self
    {
        $this->institution = $institution;

        return $this;
    }

    public function getCity() : ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getOfficePhone(): ?string
    {
        return $this->officePhone;
    }

    public function setOfficePhone(?string $officePhone): self
    {
        $this->officePhone = $officePhone;

        return $this;
    }

    public function getHomePhone(): ?string
    {
        return $this->homePhone;
    }

    public function setHomePhone(?string $homePhone): self
    {
        $this->homePhone = $homePhone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getWebpage(): ?string
    {
        return $this->webpage;
    }

    public function setWebpage(?string $webpage): self
    {
        $this->webpage = $webpage;

        return $this;
    }

    public function getReceiveEmail(): ?bool
    {
        return $this->receiveEmail;
    }

    public function setReceiveEmail(bool $receiveEmail): self
    {
        $this->receiveEmail = $receiveEmail;

        return $this;
    }

    public function getReceiveHumeStudies(): ?bool
    {
        return $this->receiveHumeStudies;
    }

    public function setReceiveHumeStudies(bool $receiveHumeStudies): self
    {
        $this->receiveHumeStudies = $receiveHumeStudies;

        return $this;
    }

    public function getMailingAddress(): ?string
    {
        return $this->mailingAddress;
    }

    public function setMailingAddress(?string $mailingAddress): self
    {
        $this->mailingAddress = $mailingAddress;

        return $this;
    }

    // getters for derivative properties
    public function isMember(): bool
    {
        return in_array('ROLE_MEMBER', $this->roles);
    }

    public function isMemberInGoodStanding(): bool
    {
        return $this->isMember() && ($this->lifetimeMember || $this->dues > new \DateTime());
    }

    public function isMemberInArrears(): bool
    {
        return $this->isMember() && (!$this->lifetimeMember && $this->dues < new \DateTime());
    }

    // needed for the security stuff
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
