<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Controller;

use DateTimeImmutable;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Repository\UserRepository;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends ControllerTestCase {
    private ?UserRepository $repository = null;

    public function testLogin() : void {
        $user = $this->repository->findOneByEmail(UserFixtures::USER['username']);
        $this->assertNull($user->getLogin());

        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Welcome');
        $user = $this->repository->findOneByEmail(UserFixtures::USER['username']);
        $this->assertNotNull($user->getLogin());
    }

    public function testDoubleLogin() : void {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
            ],
        );

        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'You are already logged in.');
    }

    public function testLoginRememberMe() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
                'remember_me' => true,
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
        $cookie = $this->client->getCookieJar()->get('NU_REMEMBER_ME');
        $this->assertNotNull($cookie);
    }

    public function testLoginRedirect() : void {
        $this->client->request('GET', '/privacy');
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects('http://localhost/privacy', Response::HTTP_FOUND);
    }

    public function testLogout() : void {
        $user = $this->repository->findOneByEmail(UserFixtures::USER['username']);
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
            ],
        );

        $this->client->submit($form);
        $crawler = $this->client->request('GET', '/logout');
        $this->assertResponseRedirects('http://localhost/');
    }

    public function testFailedLogin() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'wrongpassword',
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'Invalid credentials');
    }

    public function testInactiveLogin() : void {
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'inactive@example.com',
                'password' => 'sleeping',
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The user account is not active.');
    }

    public function testResetPassword() : void {
        $requestCrawler = $this->client->request('GET', '/request');
        $this->assertResponseIsSuccessful();

        $requestForm = $requestCrawler->selectButton('Reset')->form([
            'request_token[email]' => 'user@example.com',
        ]);
        $this->client->submit($requestForm);
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);

        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'to', 'user@example.com');
        $text = $email->getBody()->getBody();
        $matches = [];
        preg_match('|http://localhost/reset/([A-Za-z0-9-_]*)|', $text, $matches);

        $responseCrawler = $this->client->request('GET', "/reset/{$matches[1]}");
        $this->assertResponseIsSuccessful();

        $responseForm = $responseCrawler->selectButton('Reset')->form([
            'reset_password[password][first]' => 'abcdefg',
            'reset_password[password][second]' => 'abcdefg',
        ]);
        $this->client->submit($responseForm);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);

        $loginCrawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The password has been reset. You should now login to confirm.');

        $form = $loginCrawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'abcdefg',
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Welcome');
    }

    public function testResetPasswordTwice() : void {
        $requestCrawler = $this->client->request('GET', '/request');
        $this->assertResponseIsSuccessful();

        $requestForm = $requestCrawler->selectButton('Reset')->form([
            'request_token[email]' => 'user@example.com',
        ]);
        $this->client->submit($requestForm);
        $email = $this->getMailerMessage();
        $text = $email->getBody()->getBody();
        $matches = [];
        preg_match('|http://localhost/reset/([A-Za-z0-9-_]*)|', $text, $matches);

        $responseCrawler = $this->client->request('GET', "/reset/{$matches[1]}");
        $this->assertResponseIsSuccessful();

        $responseForm = $responseCrawler->selectButton('Reset')->form([
            'reset_password[password][first]' => 'abcdefg',
            'reset_password[password][second]' => 'abcdefg',
        ]);
        $this->client->submit($responseForm);
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', "/reset/{$matches[1]}");
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert:nth-child(2)', 'That security token is not valid.');
    }

    public function testResetPasswordExpiredToken() : void {
        $user = new User();
        $user->setEmail('temp@example.com');
        $user->setFullname('Test User');
        $user->setAffiliation('Institution');
        $user->setResetToken('abcdef');
        $user->setResetExpiry(new DateTimeImmutable(' - 2 days'));
        $user->setPassword('notapassword');
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('GET', '/request');
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/reset/abcdef');
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
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
        $this->em->persist($user);
        $this->em->flush();

        $this->client->request('GET', '/request');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/reset/abcdefabcdef');
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'That security token is not valid.');
    }

    public function testResetPasswordBadEmail() : void {
        $requestCrawler = $this->client->request('GET', '/request');
        $this->assertResponseIsSuccessful();

        $requestForm = $requestCrawler->selectButton('Reset')->form([
            'request_token[email]' => 'notauser@example.com',
        ]);
        $this->client->submit($requestForm);
        $this->assertResponseRedirects('/', Response::HTTP_FOUND);

        $this->assertEmailCount(0);
    }

    /**
     * @dataProvider loginRedirectData
     */
    public function testLoginRedirects(?string $referrer, string $redirect = '/') : void {
        $headers = [];
        if ($referrer) {
            $headers['HTTP_REFERER'] = $referrer;
        }
        $crawler = $this->client->request('GET', '/login', [], [], $headers);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Login')->form(
            [
                'email' => 'user@example.com',
                'password' => 'secret',
            ],
        );

        $this->client->submit($form);
        $this->assertResponseRedirects($redirect, Response::HTTP_FOUND);
    }

    public function loginRedirectData() : array {
        return [
            ['http://localhost/page/2', 'http://localhost/page/2'],
            ['http://localhost/page', 'http://localhost/page'],
            ['http://localhost/', 'http://localhost/'],
            ['http://localhost'],
            ['http://localhost/login'],
            ['http://localhost/request'],
            ['http://localhost/reset'],
            ['http://example.com/page/2'],
            ['http://localhost/not/a/path'],
            ['/page/2', '/page/2'],
            ['/login'],
            ['/request'],
            ['/reset'],
            ['/reset/somebigtokenhere'],
            ['/not/a/path'],
            ['not/a/path'],
            ['file://c/foo/b%%ar this is # a checken.'],
            ['http:/\\foo.com//ex/bob@xcma'],
            [':/\foo.com//ex/bob@xcma'], // actual terrible url
            [''],
            [null],
        ];
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repository = self::$container->get(UserRepository::class);
    }
}
