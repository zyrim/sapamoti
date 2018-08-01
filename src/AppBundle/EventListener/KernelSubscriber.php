<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\{Event\FilterResponseEvent, KernelEvents};
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * Class KernelSubscriber
 *
 * @package AppBundle\EventListener
 */
class KernelSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TokenStorageInterface
     */
    private $storage;

    /**
     * KernelSubscriber constructor.
     *
     * @param RouterInterface $router
     * @param TokenStorageInterface $storage
     */
    public function __construct(RouterInterface $router, TokenStorageInterface $storage)
    {
        $this->router  = $router;
        $this->storage = $storage;
    }

    /**
     * Check if the user is logged in.
     * If not, return to login.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $router = $this->router;
        $login  = 'security_login';

        $request = $event->getRequest();

        if ($request->get('_route') == $login) {
            return;
        }

        $token    = $this->storage->getToken();
        $response = new RedirectResponse($router->generate($login));

        if (!$token instanceof PostAuthenticationGuardToken) {
            $event->setResponse($response);
            return;
        }

        if (!$token->getUser() instanceof User) {
            $event->setResponse($response);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}