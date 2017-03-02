<?php

namespace Nines\BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadCategories;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadPages;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadStatuses;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadUsers;

class PageControllerAdminTest extends WebTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function testIndex() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 

        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/');
        $this->assertStatusCode(200, $client);
        
        $content = $crawler->text();
        $this->assertTrue(strpos($content, 'Hello world.') !== false);
        $this->assertTrue(strpos($content, 'Hello draft.') !== false);
    }
    
    public function testShowDraft() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/1');
        $this->assertStatusCode(200, $client);
    }
    
    public function testShowPublished() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/2');
        $this->assertStatusCode(200, $client);        
    }    
    
    public function testNewDefaultExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/new');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Create')->form(array(
            'page[title]' => "Test Post",
            'page[excerpt]' => '',
            'page[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $page = $em->getRepository(Page::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($page);
        $this->assertEquals('Test Post', $page->getTitle());
        $this->assertEquals('This is the content.', $page->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $page->getContent());
    }
    
    public function testNewWithExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/new');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Create')->form(array(
            'page[title]' => "Test Post",
            'page[excerpt]' => '<p>And this is the excerpt.</p>',
            'page[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $page = $em->getRepository(Page::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($page);
        $this->assertEquals('Test Post', $page->getTitle());
        $this->assertEquals('<p>And this is the excerpt.</p>', $page->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $page->getContent());
    }
    
    public function testEditDefaultExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/1/edit');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Update')->form(array(
            'page[title]' => "Test Post",
            'page[excerpt]' => '',
            'page[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $page = $em->getRepository(Page::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($page);
        $this->assertEquals('Test Post', $page->getTitle());
        $this->assertEquals('This is the content.', $page->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $page->getContent());
    }
    
    public function testEditWithExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPages::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/page/1/edit');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Update')->form(array(
            'page[title]' => "Test Post",
            'page[excerpt]' => '<p>And this is the excerpt.</p>',
            'page[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $page = $em->getRepository(Page::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($page);
        $this->assertEquals('Test Post', $page->getTitle());
        $this->assertEquals('<p>And this is the excerpt.</p>', $page->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $page->getContent());
    }
    
} 
