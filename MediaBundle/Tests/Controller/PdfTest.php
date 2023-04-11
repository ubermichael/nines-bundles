<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Tests\Controller;

use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class PdfTest extends ControllerTestCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/pdf/');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/pdf/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/pdf/');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/pdf/1');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/pdf/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/pdf/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/pdf/search');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
    }

    public function testUserSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/pdf/search');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/pdf/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'pdf',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonView() : void {
        $crawler = $this->client->request('GET', '/pdf/1/view');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAnonViewPublic() : void {
        $crawler = $this->client->request('GET', '/pdf/2/view');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testUserView() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/pdf/1/view');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testAdminView() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/pdf/1/view');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
    }

    public function testAnonThumb() : void {
        $crawler = $this->client->request('GET', '/pdf/1/thumb');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAnonThumbPublic() : void {
        $crawler = $this->client->request('GET', '/pdf/2/thumb');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'image/png');
    }

    public function testUserThumb() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/pdf/1/thumb');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'image/png');
    }

    public function testAdminThumb() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/pdf/1/thumb');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'image/png');
    }
}
