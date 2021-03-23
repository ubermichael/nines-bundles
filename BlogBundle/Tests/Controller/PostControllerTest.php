<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Tests\Controller;

use Nines\BlogBundle\DataFixtures\PostFixtures;
use Nines\BlogBundle\Entity\Post;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;

class PostControllerTest extends ControllerBaseCase
{
    protected function fixtures() : array {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/blog/post/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->filter('a.btn')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/blog/post/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->filter('a.btn')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/blog/post/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->selectLink('New')->filter('a.btn')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/blog/post/1');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('Delete')->count());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/blog/post/1');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('Delete')->count());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/blog/post/1');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->selectLink('Edit')->count());
        $this->assertSame(1, $crawler->selectLink('Delete')->count());
    }

    /**
     * @group anon
     * @group edit
     */
    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/blog/post/1/edit');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group edit
     */
    public function testUserEdit() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/blog/post/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group edit
     */
    public function testAdminEdit() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/blog/post/1/edit');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $form = $formCrawler->selectButton('Update')->form([
            // DO STUFF HERE.
            // 'posts[FIELDNAME]' => 'FIELDVALUE',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/blog/post/1'));
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        // $this->assertEquals(1, $responseCrawler->filter('td:contains("FIELDVALUE")')->count());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/blog/post/new');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNew() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/blog/post/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNew() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/blog/post/new');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $form = $formCrawler->selectButton('Create')->form([
            // DO STUFF HERE.
            // 'posts[FIELDNAME]' => 'FIELDVALUE',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        // $this->assertEquals(1, $responseCrawler->filter('td:contains("FIELDVALUE")')->count());
    }

    /**
     * @group anon
     * @group delete
     */
    public function testAnonDelete() : void {
        $crawler = $this->client->request('GET', '/blog/post/1/delete');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group delete
     */
    public function testUserDelete() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/blog/post/1/delete');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() : void {
        $preCount = count($this->entityManager->getRepository(Post::class)->findAll());
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/blog/post/1/delete');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->entityManager->clear();
        $postCount = count($this->entityManager->getRepository(Post::class)->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
