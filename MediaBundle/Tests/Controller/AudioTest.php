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

class AudioTest extends ControllerTestCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/audio/');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/audio/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/audio/');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/audio/1');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/audio/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/audio/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/audio/search');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
    }

    public function testUserSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/audio/search');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/audio/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'audio',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonPlay() : void {
        $crawler = $this->client->request('GET', '/audio/1/play');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAnonPlayPublic() : void {
        $crawler = $this->client->request('GET', '/audio/2/play');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'audio/mpeg');
    }

    public function testUserPlay() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/audio/3/play');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'audio/mpeg');
    }

    public function testAdminPlay() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/audio/3/play');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'audio/mpeg');
    }
}
