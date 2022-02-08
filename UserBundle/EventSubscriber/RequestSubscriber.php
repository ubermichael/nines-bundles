<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestSubscriber implements EventSubscriberInterface {
    use TargetPathTrait;

    private SessionInterface $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    public static function getSubscribedEvents() : array {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event) : void {
        $request = $event->getRequest();
        $attrs = $request->attributes;
        if ( ! $event->isMasterRequest()
            || $request->isXmlHttpRequest()
            || str_starts_with($attrs->get('_route'), 'nines_user_security')
            || 'security.firewall.map.context.main' !== $attrs->get('_firewall_context')) {
            return;
        }

        $this->saveTargetPath($this->session, 'main', $request->getUri());
    }
}
