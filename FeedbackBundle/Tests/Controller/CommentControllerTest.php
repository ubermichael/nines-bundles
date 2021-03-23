<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Tests\Controller;

use Nines\BlogBundle\Entity\Page;
use Nines\FeedbackBundle\DataFixtures\CommentFixtures;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;

class CommentControllerTest extends ControllerBaseCase
{
    protected function fixtures() : array {
        return [
            UserFixtures::class,
            CommentFixtures::class,
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/feedback/comment/');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->filter('a.btn')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/feedback/comment/');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->filter('a.btn')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/feedback/comment/');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->filter('a.btn')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/feedback/comment/1');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('Delete')->count());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/feedback/comment/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('Delete')->count());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/feedback/comment/1');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->selectLink('Delete')->count());
    }

    /**
     * @group anon
     * @group delete
     */
    public function testAnonDelete() : void {
        $crawler = $this->client->request('GET', '/feedback/comment/1/delete');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group delete
     */
    public function testUserDelete() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/feedback/comment/1/delete');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() : void {
        $preCount = count($this->entityManager->getRepository(Comment::class)->findAll());
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/feedback/comment/1/delete');
        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->entityManager->clear();
        $postCount = count($this->entityManager->getRepository(Comment::class)->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testPostNoComments() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/blog/page/1');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Submit')->count());
    }

    public function testPostComment() : void {
        $page = $this->entityManager->find(Page::class, 2);
        $page->setIncludeComments(true);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/blog/page/2');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $form = $crawler->selectButton('Submit')->form([
            'comment[fullname]' => 'Bobby Tables',
            'comment[email]' => 'bobby@example.com',
            'comment[followUp]' => 0,
            'comment[content]' => 'I am a banana.',
        ]);
        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/blog/page/2'));
    }
}
