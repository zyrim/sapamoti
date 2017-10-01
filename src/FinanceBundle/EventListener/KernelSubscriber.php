<?php

namespace FinanceBundle\EventListener;

use AppBundle\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class KernelSubscriber
 *
 * @package FinanceBundle\EventListener
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
     *
     * @return RedirectResponse
     */
    public function onKernelResponse()
    {
        $router = $this->container->get('router');
        $token = $this->container->get('security.token_storage')->getToken();
        $response = new RedirectResponse($router->generate('security_login'));

        if (!$token instanceof TokenInterface) {
            return $response;
        }

        if (!$token->getUser() instanceof User) {
            return $response;
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