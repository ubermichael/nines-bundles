<?php

namespace Nines\BlogBundle\Tests\Repository;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadCategories;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadPosts;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadStatuses;
use Nines\BlogBundle\Tests\DataFixtures\ORM\LoadUsers;

class PageRepositoryTest extends WebTestCase {

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
        $repo = $em->getRepository(Page::class);
        // searches that are public must include .public = 1 ?
        $query = $repo->fulltextQuery("medium", false);        
        $this->assertTrue(strpos($query->getSql(), '.public = 1') !== false);
        
        // searches that are private must not include .public = 1
        $query = $repo->fulltextQuery("medium", true);
        $this->assertTrue(strpos($query->getSql(), '.public = 1') === false);
    }        
    
}
