<?php

namespace Nines\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User.
 * 
 * Adds fullname and institution. Overrides functionality to make username 
 * and email synonymous.
 *
 * @ORM\Table(name="nines_user")
 * @ORM\Entity()
 */
class User extends BaseUser
{
    /**
     * Database ID.
     * 
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="fullname", type="string", nullable=true)
     */
    private $fullname;

    /**
     * @var string
     *
     * @ORM\Column(name="institution", type="string", nullable=true)
     */
    private $institution;

    /**
     * Construct a user.
     */
    public function __construct()
    {
        parent::__construct();
        $this->notify = false;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the email and username.
     * 
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        parent::setUsername($email);

        return parent::setEmail($email);
    }

    /**
     * Set the canonical email address.
     * 
     * @param string $emailCanonical
     *
     * @return User
     */
    public function setEmailCanonical($emailCanonical)
    {
        parent::setUsernameCanonical($emailCanonical);

        return parent::setEmailCanonical($emailCanonical);
    }

    /**
     * Set institution.
     *
     * @param string $institution
     *
     * @return User
     */
    public function setInstitution($institution)
    {
        $this->institution = $institution;

        return $this;
    }

    /**
     * Get institution.
     *
     * @return string
     */
    public function getInstitution()
    {
        return $this->institution;
    }

    /**
     * Set fullname.
     *
     * @param string $fullname
     *
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * Get fullname.
     *
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }
}
