<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Controller;

use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Repository\UserRepository;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends ControllerTestCase {
    private ?UserRepository $repository = null;

    public function testUserIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/profile/');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/profile/');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Save')->form([
            'profile[email]' => 'other@example.com',
            'profile[fullname]' => 'New Name',
            'profile[affiliation]' => 'New Department',
            'profile[password]' => 'secret',
        ]);
        $responseCrawler = $this->client->submit($form);
        $this->assertResponseRedirects('/profile/', Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'Your profile has been updated.');

        $user = $this->repository->findOneByEmail('other@example.com');
        $this->assertNotNull($user);
    }

    public function testUserEditWrongPassword() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/profile/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $form = $crawler->selectButton('Save')->form([
            'profile[email]' => 'other@example.com',
            'profile[fullname]' => 'Other User',
            'profile[affiliation]' => 'Institution',
            'profile[password]' => 'wrongpassword',
        ]);
        $responseCrawler = $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('div.alert', 'The password does not match');
        $user = $this->repository->findOneByEmail('other@example.com');

        $this->assertNull($user);
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/profile/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserChangePassword() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/profile/password');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $form = $crawler->selectButton('Save')->form([
            'change_password[current_password]' => 'secret',
            'change_password[new_password][first]' => 'othersecret',
            'change_password[new_password][second]' => 'othersecret',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/profile/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'Your password has been updated.');

        $encoder = $this->client->getContainer()->get('security.password_encoder');

        // Refresh the user from the database.
        $changedUser = $this->repository->findOneByEmail(UserFixtures::USER['username']);
        $this->assertTrue($encoder->isPasswordValid($changedUser, 'othersecret'));
    }

    public function testUserChangePasswordFail() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/profile/password');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $form = $crawler->selectButton('Save')->form([
            'change_password[current_password]' => 'badpassword',
            'change_password[new_password][first]' => 'othersecret',
            'change_password[new_password][second]' => 'othersecret',
        ]);
        $this->client->submit($form);
        $this->assertSelectorTextContains('div.alert', 'The password does not match.');
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repository = self::$container->get(UserRepository::class);
    }
}
