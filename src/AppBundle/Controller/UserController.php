<?php

namespace AppBundle\Controller;

use AppBundle\Form\ChangePasswordForm;
use AppBundle\Form\UserForm;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 *
 * @package AppBundle\Controller
 */
class UserController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var UserRepository
     */
    protected $repository;

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/user/edit", name="user_edit")
     */
    public function editAction(Request $request): Response
    {
        $user = $this->user();
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            if (!$user->getEmail()) {
                $form->addError(new FormError('Das Email-Feld darf nicht leer sein.'));
            }

            $this->entityManager()->flush($user);
        }

        $form->createView();

        return $this->render('user/user.html.twig', [
            'title'    => 'Profil bearbeiten',
            'template' => 'user/edit.html.twig',
            'form'     => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     *
     * @Route("/user/change-password", name="user_change_password")
     */
    public function changePasswordAction(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->user();
        $form = $this->createForm(ChangePasswordForm::class);

        $form->handleRequest($request);

        $template = 'user/user.html.twig';
        $values   = [
            'title'    => 'Passwort ändern',
            'template' => 'user/change_password.html.twig',
            'form'     => $form->createView(),
        ];
        $success  = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$encoder->isPasswordValid($user, $data['_oldPassword'])) {
                $form->addError(new FormError('Das alte Passwort ist nicht korrekt.'));

                $values['form'] = $form->createView();

                return $this->render($template, $values);
            }

            if ($data['_newPassword'] != $data['_repeatNewPassword']) {
                $form->addError(new FormError('Die neuen Passwörter stimmen nicht überein.'));

                $values['form'] = $form->createView();

                return $this->render($template, $values);
            }

            $user->setPlainPassword($data['_newPassword']);
            $this->entityManager()->flush($user);

            $success = true;
        }

        $values['success'] = $success;

        return $this->render($template, $values);
    }

    /**
     * @return Response|User
     */
    protected function user()
    {
        if (!$this->user && !($this->user = $this->getUser()) instanceof User) {
            return $this->redirectToRoute('security_login');
        }

        return $this->user;
    }

    /**
     * @return EntityManager
     */
    protected function entityManager(): EntityManager#
    {
        if (!$this->em) {
            $this->em = $this->getDoctrine()->getManager();
        }

        return $this->em;
    }

    /**
     * @return UserRepository
     */
    protected function repository(): UserRepository
    {
        if (!$this->repository) {
            $this->repository = $this->entityManager()->getRepository(User::class);
        }

        return $this->repository;
    }
}