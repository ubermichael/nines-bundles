<?php

namespace Nines\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Concrete User.
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
     * Any extra data associated with the user.
     * 
     * @var array
     * @ORM\Column(name="data", type="array")
     */
    private $data;

    /**
     * Construct a user.
     */
    public function __construct()
    {
        parent::__construct();
        $this->data = array();
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
    
    /**
     * Get some data associated with the user.
     * 
     * @param string $key
     * @return mixed
     */
    public function getData($key) {
        if(isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }
    
    /**
     * Check if a user has some data. Checks that the key is defined 
     * and the value associated with the key is not null. False and
     * the empty string are considered valid.
     * 
     * @param type $key
     */
    public function hasData($key) {
        return isset($this->data[$key]) && $this->data[$key] !== null;
    }
    
    /**
     * Store some data with the user.
     * 
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }
}
