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

class AdminControllerTest extends ControllerBaseCase {
    protected function fixtures() : array {
        return [
            UserFixtures::class,
        ];
    }

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/admin/user/');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserIndex() : void {
        $user = $this->login('user.user');
        $crawler = $this->client->request('GET', '/admin/user/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminIndex() : void {
        $user = $this->login('user.admin');
        $crawler = $this->client->request('GET', '/admin/user/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAnonShow() : void {
        $user = $this->references->getReference('user.user');
        $crawler = $this->client->request('GET', '/admin/user/' . $user->getId());
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserShow() : void {
        $user = $this->login('user.user');
        $crawler = $this->client->request('GET', '/admin/user/' . $user->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminShow() : void {
        $id = $this->references->getReference('user.user')->getId();
        $user = $this->login('user.admin');
        $crawler = $this->client->request('GET', '/admin/user/' . $id);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/admin/user/new');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $user = $this->login('user.user');
        $crawler = $this->client->request('GET', '/admin/user/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminNew() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/admin/user/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Save')->form([
            'user[active]' => 1,
            'user[email]' => 'new@example.com',
            'user[fullname]' => 'New User',
            'user[affiliation]' => 'Something',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The user account has been created with a random password.');

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'new@example.com',
        ]);
        $this->assertNotNull($user);
    }

    public function testAnonEdit() : void {
        $user = $this->references->getReference('user.user');
        $crawler = $this->client->request('GET', '/admin/user/' . $user->getId() . '/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $user = $this->login('user.user');
        $crawler = $this->client->request('GET', '/admin/user/' . $user->getId() . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminEdit() : void {
        $id = $this->references->getReference('user.user')->getId();
        $user = $this->login('user.admin');
        $crawler = $this->client->request('GET', '/admin/user/' . $id . '/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Update')->form([
            'user[active]' => 1,
            'user[email]' => 'new@example.com',
            'user[fullname]' => 'New User',
            'user[affiliation]' => 'Something',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user/', Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'The user account has been updated.');

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'new@example.com',
        ]);
        $this->assertNotNull($user);
    }

    public function testAnonDelete() : void {
        $user = $this->references->getReference('user.user');
        $crawler = $this->client->request('DELETE', '/admin/user/' . $user->getId());
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDelete() : void {
        $user = $this->login('user.user');
        $crawler = $this->client->request('DELETE', '/admin/user/' . $user->getId());
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminDelete() : void {
        $id = $this->references->getReference('user.user')->getId();
        $user = $this->login('user.admin');
        $crawler = $this->client->request('GET', '/admin/user/' . $id . '');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Delete')->form([]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user/', Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'The account has been removed.');

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'user@example.com',
        ]);
        $this->assertNull($user);
    }
}
