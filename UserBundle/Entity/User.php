<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Nines\UserBundle\Repository\UserRepository")
 * @ORM\Table(name="nines_user")
 */
class User extends AbstractEntity implements UserInterface {
    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $active = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\Email
     */
    private ?string $email = null;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private ?string $password = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $resetToken = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeInterface $resetExpiry = null;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank
     */
    private ?string $fullname = null;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank
     */
    private ?string $affiliation = null;

    /**
     * @ORM\Column(type="array")
     *
     * @var array<string>
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?DateTimeInterface $login;

    public function __construct() {
        parent::__construct();
        $this->active = false;
        $this->login = null;
        $this->roles = ['ROLE_USER'];
    }

    public function __toString() : string {
        return $this->fullname;
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
     *
     * @return array<string>
     */
    public function getRoles() : array {
        if ( ! in_array('ROLE_USER', $this->roles, true)) {
            $this->roles[] = 'ROLE_USER';
        }

        return $this->roles;
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles) : self {
        $this->roles = $roles;
        if ( ! in_array('ROLE_USER', $roles, true)) {
            $this->roles[] = 'ROLE_USER';
        }

        return $this;
    }

    public function addRole(string $role) : self {
        if ( ! in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role) : self {
        if ('ROLE_USER' !== $role && in_array($role, $this->roles, true)) {
            array_splice($this->roles, array_search($role, $this->roles, true), 1);
        }

        return $this;
    }

    public function hasRole(string $role) : bool {
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
    public function getSalt() : ?string {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials() : void {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getResetExpiry() : ?DateTimeInterface {
        return $this->resetExpiry;
    }

    public function setResetExpiry(?DateTimeInterface $resetExpiry) : self {
        $this->resetExpiry = $resetExpiry;

        return $this;
    }

    public function getLogin() : ?DateTimeInterface {
        return $this->login;
    }

    public function setLogin(?DateTimeInterface $login) : self {
        $this->login = $login;

        return $this;
    }
}
