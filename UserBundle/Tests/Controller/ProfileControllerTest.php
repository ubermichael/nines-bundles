<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Controller;

use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends ControllerBaseCase {
    protected function fixtures() : array {
        return [
            UserFixtures::class,
        ];
    }

    public function testUserIndex() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/profile/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/profile/');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/profile/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
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

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'other@example.com',
        ]);
        $this->assertNotNull($user);
    }

    public function testUserEditWrongPassword() : void {
        $this->login('user.user');
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
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'other@example.com',
        ]);
        $this->assertNull($user);
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/profile/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserChangePassword() : void {
        $user = $this->login('user.user');
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
        $changedUser = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $user->getEmail(),
        ]);
        $this->assertTrue($encoder->isPasswordValid($changedUser, 'othersecret'));
    }

    public function testUserChangePasswordFail() : void {
        $user = $this->login('user.user');
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
}
