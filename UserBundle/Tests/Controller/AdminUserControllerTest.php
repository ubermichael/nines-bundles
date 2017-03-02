<?php

namespace Nines\UserBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Tests\DataFixtures\ORM\LoadUsers;
use Symfony\Component\HttpFoundation\Response;

class AdminUserControllerTest extends WebTestCase {

	protected $client;
	
	public function setUp() {
		parent::setUp();
		$this->client = static::createClient(array(), array(
                'PHP_AUTH_USER' => 'admin@example.com',
                'PHP_AUTH_PW' => 'supersecret',
		));
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
	}
	
	public function testIndex() {
        $this->loadFixtures(array(
            LoadUsers::class
        )); 
         
		$this->client->request('GET', '/admin/user/');
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$this->assertContains('admin@example.com', $this->client->getResponse()->getContent());
		$this->assertContains('user@example.com', $this->client->getResponse()->getContent());
	}
	
	public function testCreate() {
		$this->client->request('GET', 'admin/user/new');
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->getCrawler();
        $formCrawler = $crawler->selectButton('Create');
		$form = $formCrawler->form(array(
			'admin_user[email]' => 'bob@example.com',
			'admin_user[fullname]' => 'Bob Terwilliger',
			'admin_user[institution]' => 'Springfield State Penn',
			'admin_user[enabled]' => 1,
		));
		$this->client->submit($form);
		
		$this->em->clear();
		$user = $this->em->getRepository('NinesUserBundle:User')->findOneBy(array(
			'email' => 'bob@example.com',
		));
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('bob@example.com', $user->getUsername());
		$this->assertEquals('bob@example.com', $user->getEmail());
		$this->assertEquals('Bob Terwilliger', $user->getFullname());
		$this->assertEquals('Springfield State Penn', $user->getInstitution());
	}
	
	public function testEdit() {
		$this->client->request('GET', 'admin/user/2/edit');
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->getCrawler();
        $formCrawler = $crawler->selectButton('Update');
		$form = $formCrawler->form(array(
			'admin_user[email]' => 'bart@example.com',
			'admin_user[fullname]' => 'Bart Simpson',
			'admin_user[institution]' => 'Springfield Elementary',
			'admin_user[enabled]' => 1,
		));
		$this->client->submit($form);
		
		$this->em->clear();
		$user = $this->em->getRepository('NinesUserBundle:User')->findOneBy(array(
			'email' => 'bart@example.com',
		));
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('bart@example.com', $user->getUsername());
		$this->assertEquals('bart@example.com', $user->getEmail());
		$this->assertEquals('Bart Simpson', $user->getFullname());
		$this->assertEquals('Springfield Elementary', $user->getInstitution());
	}

	public function testDelete() {
		$this->client->followRedirects(true);
		$this->client->request('GET', 'admin/user/2/delete');
		$this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
		$this->em->clear();
		$user = $this->em->getRepository('NinesUserBundle:User')->findOneBy(array(
			'email' => 'bart@example.com',
		));
		$this->assertNull($user);
	}
}
