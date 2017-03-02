<?php


namespace Nines\UserBundle\Tests\Entity;

use Nines\UserBundle\Entity\User;
use PHPUnit_Framework_TestCase;

class UserTest extends PHPUnit_Framework_TestCase {

	protected $user;
	
	public function setUp() {
		$this->user = new User();
	}
	
	public function testUsername() {
		$this->user->setEmail('u@example.com');
		$this->assertEquals('u@example.com', $this->user->getUsername());
	}
}
