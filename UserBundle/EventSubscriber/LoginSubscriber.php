<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\EventSubscriber;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSubscriber implements EventSubscriberInterface {
    private ?EntityManagerInterface $em = null;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public static function getSubscribedEvents() : array {
        return [
            'security.interactive_login' => 'onSecurityInteractiveLogin',
        ];
    }

    /**
     * @throws Exception
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) : void {
        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        $user->setLogin(new DateTimeImmutable());
        $this->em->flush();
    }
}
