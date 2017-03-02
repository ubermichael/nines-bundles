<?php

namespace Nines\BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadPages;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadUsers;

class PageControllerAnonTest extends WebTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function testIndex() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        )); 
 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/');
        $this->assertStatusCode(200, $client);
        
        $content = $crawler->text(); 
        $this->assertTrue(strpos($content, 'Hello world.') !== false);
        $this->assertTrue(strpos($content, 'Hello draft.') === false);
    }
     
    public function testFullText() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        ));
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/search');
        $this->assertStatusCode(200, $client);
        // further testing requires mysql. 
    }
    
    public function testAnonNew() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        ));
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/new');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }
     
    public function testAnonShowPrivate() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        ));
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/1');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }
    
    public function testAnonShowPublic() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        ));
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/2');
        $this->assertStatusCode(200, $client);        
    }
     
    public function testAnonEdit() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        ));
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/1/edit');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));        
        
        $crawler = $client->request('POST', '/page/1/edit');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));        
    }
    
    public function testAnonDelete() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadPages::class,
        ));
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/1/delete');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

    
} 
