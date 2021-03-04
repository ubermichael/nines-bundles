<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\EventSubscriber;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSubscriber implements EventSubscriberInterface {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'security.interactive_login' => 'onSecurityInteractiveLogin',
        ];
    }

    /**
     * @throws Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) : void {
        $user = $event->getAuthenticationToken()->getUser();
        $user->setLogin(new DateTimeImmutable());
        $this->em->flush();
    }
}
