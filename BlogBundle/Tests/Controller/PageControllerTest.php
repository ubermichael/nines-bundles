<?php

namespace Nines\BlogBundle\Tests\Controller;

use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\DataFixtures\ORM\LoadPage;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

class PageControllerTest extends BaseTestCase
{

    protected function getFixtures() {
        return [
            LoadUser::class,
            LoadPage::class
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/page/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/page/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('New')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/2'); // 2 is public.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
        $this->assertEquals(0, $crawler->selectLink('Delete')->count());

        $crawler = $client->request('GET', '/page/1'); // 1 is private
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/page/1'); // 1 is private.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
        $this->assertEquals(0, $crawler->selectLink('Delete')->count());

        $crawler = $client->request('GET', '/page/2'); // 1 is private
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/page/1'); // 1 is private.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('Edit')->count());
        $this->assertEquals(1, $crawler->selectLink('Delete')->count());

        $crawler = $client->request('GET', '/page/2'); // 1 is private
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @group anon
     * @group edit
     */
    public function testAnonEdit() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/1/edit');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group edit
     */
    public function testUserEdit() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/page/1/edit');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group edit
     */
    public function testAdminEdit() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/page/1/edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $form = $formCrawler->selectButton('Update')->form([
            // DO STUFF HERE.
            // 'pages[FIELDNAME]' => 'FIELDVALUE',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/page/1'));
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // $this->assertEquals(1, $responseCrawler->filter('td:contains("FIELDVALUE")')->count());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNew() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/new');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNew() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/page/new');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNew() {
        $client = $this->makeClient(LoadUser::ADMIN);
        $formCrawler = $client->request('GET', '/page/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
        $form = $formCrawler->selectButton('Create')->form([
            // DO STUFF HERE.
            // 'pages[FIELDNAME]' => 'FIELDVALUE',
        ]);

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // $this->assertEquals(1, $responseCrawler->filter('td:contains("FIELDVALUE")')->count());
    }

    /**
     * @group anon
     * @group delete
     */
    public function testAnonDelete() {
        $client = $this->makeClient();
        $crawler = $client->request('GET', '/page/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group delete
     */
    public function testUserDelete() {
        $client = $this->makeClient(LoadUser::USER);
        $crawler = $client->request('GET', '/page/1/delete');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() {
        $preCount = count($this->em->getRepository(Page::class)->findAll());
        $client = $this->makeClient(LoadUser::ADMIN);
        $crawler = $client->request('GET', '/page/1/delete');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertTrue($client->getResponse()->isRedirect());
        $responseCrawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->em->clear();
        $postCount = count($this->em->getRepository(Page::class)->findAll());
        $this->assertEquals($preCount - 1, $postCount);
    }

}
