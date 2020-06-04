<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests;

use DateTimeImmutable;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends ControllerBaseCase {
    protected function fixtures() : array {
        return [
            UserFixtures::class,
        ];
    }

    public function testLogin() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
            ]
        );

        $this->client->submit($form);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect('/'));
        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Hello');
    }

    public function testLoginRememberMe() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
                'remember_me' => true,
            ]
        );

        $this->client->submit($form);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect('/'));
        $cookie = $this->client->getCookieJar()->get('NU_REMEMBER_ME');
        $this->assertNotNull($cookie);
    }

    public function testFailedLogin() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form(
            [
                'email' => 'user@example.com',
                'password' => 'wrongpassword',
            ]
        );

        $this->client->submit($form);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'Invalid credentials');
    }

    public function testInactiveLogin() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('submit')->form(
            [
                'email' => 'inactive@example.com',
                'password' => 'sleeping',
            ]
        );

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'Account inactive@example.com is not active.');
    }

    public function testResetPassword() : void {
        $requestCrawler = $this->client->request('GET', '/request');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $requestForm = $requestCrawler->selectButton('Request')->form([
            'request_token[email]' => 'user@example.com',
        ]);
        $this->client->submit($requestForm);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'to', 'user@example.com');
        $text = $email->getBody()->getBody();
        $matches = [];
        preg_match('|http://localhost/reset/([A-Za-z0-9-_]*)|', $text, $matches);

        $responseCrawler = $this->client->request('GET', "/reset/{$matches[1]}");
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $responseForm = $responseCrawler->selectButton('Reset')->form([
            'reset_password[password][first]' => 'abcdefg',
            'reset_password[password][second]' => 'abcdefg',
        ]);
        $this->client->submit($responseForm);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect('/login'));

        $loginCrawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The password has been reset. You should now login to confirm.');

        $form = $loginCrawler->selectButton('submit')->form(
            [
                'email' => 'user@example.com',
                'password' => 'abcdefg',
            ]
        );

        $this->client->submit($form);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect('/'));
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Hello');
    }

    public function testResetPasswordExpiredToken() : void {
        $user = new User();
        $user->setEmail('temp@example.com');
        $user->setFullname('Test User');
        $user->setAffiliation('Institution');
        $user->setResetToken('abcdef');
        $user->setResetExpiry(new DateTimeImmutable(' - 2 days'));
        $user->setPassword('notapassword');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('GET', '/request');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/reset/abcdef');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The security token has expired. Please try again.');
    }

    public function testResetPasswordWrongToken() : void {
        $user = new User();
        $user->setEmail('temp@example.com');
        $user->setFullname('Test User');
        $user->setAffiliation('Institution');
        $user->setResetToken('abcdef');
        $user->setResetExpiry(new DateTimeImmutable(' + 1 days'));
        $user->setPassword('notapassword');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->request('GET', '/request');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/reset/abcdefabcdef');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'That security token is not valid.');
    }

    public function testResetPasswordBadEmail() : void {
        $requestCrawler = $this->client->request('GET', '/request');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $requestForm = $requestCrawler->selectButton('Request')->form([
            'request_token[email]' => 'notauser@example.com',
        ]);
        $this->client->submit($requestForm);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->assertEmailCount(0);
    }
}
