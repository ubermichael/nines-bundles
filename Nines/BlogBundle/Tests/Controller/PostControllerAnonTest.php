<?php

namespace Nines\BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadCategories;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadPosts;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadStatuses;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadUsers;

class PostControllerAnonTest extends WebTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function testIndex() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        ));
 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/');
        $this->assertStatusCode(200, $client);
        
        $content = $crawler->text();
        $this->assertTrue(strpos($content, 'Hello world.') !== false);
        $this->assertTrue(strpos($content, 'Hello draft.') === false);
    }
    
    public function testFullText() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/fulltext');
        $this->assertStatusCode(200, $client);
        // further testing requires mysql. 
    }
    
    public function testAnonNew() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/new');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }
    
    public function testAnonShowDraft() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/1');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }
    
    public function testAnonShowPublished() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/2');
        $this->assertStatusCode(200, $client);        
    }
    
    public function testAnonEdit() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/1/edit');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));        
        
        $crawler = $client->request('POST', '/post/1/edit');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));        
    }
    
    public function testAnonDelete() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/post/1/delete');
        $this->assertStatusCode(302, $client);
        $this->assertStringEndsWith('/login', $client->getResponse()->headers->get('Location'));
    }

    
} 
