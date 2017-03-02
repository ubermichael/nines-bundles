<?php

namespace Nines\BlogBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadCategories;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadPosts;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadStatuses;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadUsers;

class PostControllerAdminTest extends WebTestCase {

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

        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/');
        $this->assertStatusCode(200, $client);
        
        $content = $crawler->text();
        $this->assertTrue(strpos($content, 'Hello world.') !== false);
        $this->assertTrue(strpos($content, 'Hello draft.') !== false);
    }
    
    public function testAnonShowDraft() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/1');
        $this->assertStatusCode(200, $client);
    }
    
    public function testAnonShowPublished() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/2');
        $this->assertStatusCode(200, $client);        
    }    
    
    public function testNewDefaultExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/new');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Create')->form(array(
            'post[title]' => "Test Post",
            'post[category]' => 1,
            'post[status]' => 1,
            'post[excerpt]' => '',
            'post[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $post = $em->getRepository(Post::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($post);
        $this->assertEquals('Test Post', $post->getTitle());
        $this->assertEquals(1, $post->getCategory()->getId());
        $this->assertEquals(1, $post->getStatus()->getId());
        $this->assertEquals('This is the content.', $post->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $post->getContent());
    }
    
    public function testNewWithExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/new');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Create')->form(array(
            'post[title]' => "Test Post",
            'post[category]' => 1,
            'post[status]' => 1,
            'post[excerpt]' => '<p>And this is the excerpt.</p>',
            'post[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $post = $em->getRepository(Post::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($post);
        $this->assertEquals('Test Post', $post->getTitle());
        $this->assertEquals(1, $post->getCategory()->getId());
        $this->assertEquals(1, $post->getStatus()->getId());
        $this->assertEquals('<p>And this is the excerpt.</p>', $post->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $post->getContent());
    }
    
    public function testEditDefaultExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/1/edit');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Update')->form(array(
            'post[title]' => "Test Post",
            'post[category]' => 1,
            'post[status]' => 1,
            'post[excerpt]' => '',
            'post[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $post = $em->getRepository(Post::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($post);
        $this->assertEquals('Test Post', $post->getTitle());
        $this->assertEquals(1, $post->getCategory()->getId());
        $this->assertEquals(1, $post->getStatus()->getId());
        $this->assertEquals('This is the content.', $post->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $post->getContent());
    }
    
    public function testEditWithExcerpt() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        )); 
        $client = $this->makeClient(array(
            'username' => 'admin@example.com',
            'password' => 'supersecret',
        ));
        $crawler = $client->request('GET', '/post/1/edit');
        $this->assertStatusCode(200, $client);
        
        $form = $crawler->selectButton('Update')->form(array(
            'post[title]' => "Test Post",
            'post[category]' => 1,
            'post[status]' => 1,
            'post[excerpt]' => '<p>And this is the excerpt.</p>',
            'post[content]' => '<p>This is the content.</p>',
        ));
        $crawler = $client->submit($form);
        $this->assertStatusCode(302, $client);
        
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $em->clear();
        
        $post = $em->getRepository(Post::class)->findOneBy(array(
            'title' => 'Test Post',
        ));
        $this->assertNotNull($post);
        $this->assertEquals('Test Post', $post->getTitle());
        $this->assertEquals(1, $post->getCategory()->getId());
        $this->assertEquals(1, $post->getStatus()->getId());
        $this->assertEquals('<p>And this is the excerpt.</p>', $post->getExcerpt());
        $this->assertEquals('<p>This is the content.</p>', $post->getContent());
    }
    
} 
