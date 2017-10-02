<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class KernelSubscriber
 *
 * @package AppBundle\EventListener
 */
class KernelSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * UserAgentSubscriber constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Check if the user is logged in.
     * If not, return to login.
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $router = $this->container->get('router');
        $login = 'security_login';

        $request = $event->getRequest();
        
        if ($request->get('_route') == $login) {
            return;
        }

        $token = $this->container->get('security.token_storage')->getToken();
        $response = new RedirectResponse($router->generate('security_login'));

        if (!$token instanceof TokenInterface) {
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
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}