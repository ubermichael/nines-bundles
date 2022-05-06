<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\TestCase;

use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Repository\UserRepository;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class ControllerTestCase extends WebTestCase {
    protected ?KernelBrowser $client = null;

    protected ?EntityManagerInterface $em = null;

    /**
     * @param ?array<string,string> $credentials
     */
    protected function login(?array $credentials = null) : void {
        $this->client->restart();
        if ($credentials) {
            $session = self::$container->get('session');
            /** @var UserRepository $repository */
            $repository = $this->em->getRepository(User::class);
            $user = $repository->findOneByEmail($credentials['username']);
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $session->set('_security_main', serialize($token));
            $session->save();
            $cookie = new Cookie($session->getName(), $session->getId());
            $this->client->getCookieJar()->set($cookie);
            self::$container->get('security.token_storage')->setToken($token);
        }
    }

    protected function addField(Crawler $crawler, string $formName, string $name, string $value = '', string $type = 'text') : void {
        $doc = $crawler->getNode(0)->ownerDocument;
        $input = $doc->createElement('input');
        $input->setAttribute('name', $name);
        $input->setAttribute('type', $type);
        $input->setAttribute('value', $value);
        $formNode = $crawler->filter("form[name='{$formName}']")->getNode(0);
        $formNode->appendChild($input);
    }

    /**
     * @param Form|FormField $form
     * @param mixed $value
     */
    protected function overrideField($form, string $fieldName, $value) : void {
        $form[$fieldName]->disableValidation()->setValue($value);
    }

    protected function reset() : void {
        StaticDriver::rollBack();
        StaticDriver::beginTransaction();
    }

    protected function commit() : void {
        StaticDriver::commit();
    }

    protected function dumpResult() : void {
        try {
            fwrite(STDERR, Html2Text::convert($this->client->getResponse()->getContent(), ['ignore_errors' => true]));
        } catch (Html2TextException $e) {
            fwrite(STDERR, 'Cannot extract text from response: ' . $e->getMessage());
        }
    }

    protected function setUp() : void {
        $this->client = static::createClient();
        $this->em = static::$container->get(EntityManagerInterface::class);
    }
}
