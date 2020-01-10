<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="Nines\UserBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active;

    /** @var string
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $resetToken;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $resetExpiry;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $fullname;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $affiliation;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $login;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private $created;

    /**
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=false)
     */
    private $updated;

    public function __construct() {
        $this->active = false;
        $this->roles = ['ROLE_USER'];
    }

    public function __toString() : string {
        return $this->email;
    }

    /**
     * Alias for getEmail.
     *
     * @see UserInterface
     */
    public function getUsername() : string {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles() : array {
        return $this->roles;
    }

    public function setRoles(array $roles) : self {
        $this->roles = $roles;
        if ( ! in_array('ROLE_USER', $roles, true)) {
            $this->roles[] = 'ROLE_USER';
        }

        return $this;
    }

    public function addRole($role) : self {
        if ( ! in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole($role) : self {
        if ('ROLE_USER' !== $role && in_array($role, $this->roles, true)) {
            array_splice($this->roles, array_search($role, $this->roles, true), 1);
        }

        return $this;
    }

    public function hasRole($role) : bool {
        return in_array($role, $this->roles, true);
    }

    /**
     * @see UserInterface
     */
    public function getPassword() : string {
        return (string) $this->password;
    }

    public function setPassword(string $password) : self {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt() : void {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() : void {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getId() : ?int {
        return $this->id;
    }

    public function isActive() : ?bool {
        return $this->active;
    }

    public function setActive(bool $active) : self {
        $this->active = $active;

        return $this;
    }

    public function getEmail() : ?string {
        return $this->email;
    }

    public function setEmail(string $email) : self {
        $this->email = $email;

        return $this;
    }

    public function getFullname() : ?string {
        return $this->fullname;
    }

    public function setFullname(string $fullname) : self {
        $this->fullname = $fullname;

        return $this;
    }

    public function getAffiliation() : ?string {
        return $this->affiliation;
    }

    public function setAffiliation(string $affiliation) : self {
        $this->affiliation = $affiliation;

        return $this;
    }

    public function getResetToken() : ?string {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken) : self {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getResetExpiry() : ?DateTimeImmutable {
        return $this->resetExpiry;
    }

    public function setResetExpiry(?DateTimeImmutable $resetExpiry) : self {
        $this->resetExpiry = $resetExpiry;

        return $this;
    }

    public function getLogin() : ?DateTimeImmutable {
        return $this->login;
    }

    public function setLogin(?DateTimeImmutable $login) : self {
        $this->login = $login;

        return $this;
    }

    public function getCreated() : ?DateTimeImmutable {
        return $this->created;
    }

    /**
     * @ORM\PrePersist
     *
     * @throws Exception
     *
     * @return User
     */
    public function setCreated() : self {
        if ( ! $this->created) {
            $this->created = new DateTimeImmutable();
            $this->updated = new DateTimeImmutable();
        }

        return $this;
    }

    public function getUpdated() : ?DateTimeImmutable {
        return $this->updated;
    }

    /**
     * @ORM\PreUpdate
     *
     * @throws Exception
     *
     * @return User
     */
    public function setUpdated() : self {
        $this->updated = new DateTimeImmutable();

        return $this;
    }
}
