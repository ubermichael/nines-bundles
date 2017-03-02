<?php

namespace Nines\BlogBundle\Tests\Repository;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadCategories;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadPosts;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadStatuses;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadUsers;

class PostRepositoryTest extends WebTestCase {

    public function setUp() {
        parent::setUp();
        self::bootKernel();
    }

    public function testFulltext() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        ));
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository(Post::class);
        // searches that are not private must include .status_id = ?
        $query = $repo->fulltextQuery("medium", false);
        $this->assertTrue(strpos($query->getSql(), '.status_id = ?') !== false);
        
        // searches that are public must not include .status_id = ?
        $query = $repo->fulltextQuery("medium", true);
        $this->assertTrue(strpos($query->getSql(), '.status_id = ?') === false);
    }        
    
    public function testRecent() {
        $this->loadFixtures(array(
            LoadUsers::class,
            LoadStatuses::class,
            LoadCategories::class,
            LoadPosts::class,
        ));
        $em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository(Post::class);
        // searches that are not private must include .status_id = ?
        $query = $repo->recentQuery(false);
        $this->assertTrue(strpos($query->getSql(), '.status_id = ?') !== false);
        
        // searches that are public must not include .status_id = ?
        $query = $repo->recentQuery(true);
        $this->assertTrue(strpos($query->getSql(), '.status_id = ?') === false);
    }        
}
