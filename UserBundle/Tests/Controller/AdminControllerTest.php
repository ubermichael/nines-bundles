<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Nines\BlogBundle\Entity\Post;
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Repository\UserRepository;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends ControllerTestCase {
    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/admin/user/');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/admin/user/');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/admin/user/');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/admin/user/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/admin/user/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/admin/user/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/admin/user/new');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/admin/user/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/admin/user/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form([
            'user[active]' => 1,
            'user[email]' => 'new@example.com',
            'user[fullname]' => 'New User',
            'user[affiliation]' => 'Something',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The user account has been created with a random password.');

        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => 'new@example.com',
        ]);
        $this->assertNotNull($user);
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/admin/user/1/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/admin/user/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/admin/user/2/edit');
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
        $this->assertSelectorTextContains('div.alert', 'The user account has been updated.');

        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => 'new@example.com',
        ]);
        $this->assertNotNull($user);
    }

    public function testAnonDelete() : void {
        $crawler = $this->client->request('DELETE', '/admin/user/2');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDelete() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/admin/user/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function testAdminDelete() : void {
        // Remove the notes and posts which may cause deletion to fail.
        foreach ($this->em->getRepository(CommentNote::class)->findAll() as $note) {
            $this->em->remove($note);
        }
        foreach ($this->em->getRepository(Post::class)->findAll() as $post) {
            $this->em->remove($post);
        }
        $this->em->flush();

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/admin/user/2');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Delete')->form([]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user/', Response::HTTP_FOUND);
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'The account has been removed.');

        $user = $this->em->getRepository(User::class)->findOneBy([
            'email' => 'user@example.com',
        ]);
        $this->assertNull($user);
        $this->reset();
    }

    public function testAnonPassword() : void {
        $crawler = $this->client->request('GET', '/admin/user/1/password');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserPassword() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/admin/user/1/password');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminPassword() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/admin/user/2/password');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('Update')->form([
            'user_password[new_password][first]' => 'abc',
            'user_password[new_password][second]' => 'abc',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/admin/user/', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('div.alert', 'The user password has been updated.');

        $encoder = $this->client->getContainer()->get('security.password_encoder');
        // Refresh the user from the database.
        $changedUser = self::$container->get(UserRepository::class)->findOneByEmail(UserFixtures::USER['username']);
        $this->assertTrue($encoder->isPasswordValid($changedUser, 'abc'));
    }
}
