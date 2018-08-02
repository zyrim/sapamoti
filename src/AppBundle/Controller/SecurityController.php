<?php

namespace AppBundle\Controller;

use AppBundle\Form\LoginForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 *
 * @package AppBundle\Controller
 */
class SecurityController extends Controller
{
    /**
     * @param AuthenticationUtils $authUtils
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/login", name="security_login")
     */
    public function loginAction(AuthenticationUtils $authUtils): Response
    {
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername
        ]);

        return $this->render('security/login.html.twig', [
            'form'  => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     *
     * @throws \Exception
     */
    public function logoutAction()
    {
        throw new \Exception('this should not be reached!');
    }
}