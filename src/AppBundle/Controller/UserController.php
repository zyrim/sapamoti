<?php
/**
 * AppBundle
 *
 * @namespace
 */
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserForm;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class UserController
 *
 * @package AppBundle\Controller
 */
class UserController extends Controller
{
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
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/signup", name="signup")
     */
    public function signupAction(Request $request)
    {
        $user = new User();
        $user->setFirstname('Max')
            ->setLastname('Mustermann')
            ->setEmail('max.mustermann@mail.com')
            ->setPassword('');

        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            if ($this->validate($form, $user)) {
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();

                return $this->redirectToRoute('login');
            }
        }

        return $this->render('user/signup.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/login", name="login")
     */
    protected function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @param Form $form
     * @param User $user
     * @return bool
     */
    protected function validate(Form $form, User $user)
    {
        if ($this->repository->findOneBy(['email' => $user->getEmail()])) {
            $form->get('email')->addError(
                new FormError(
                    sprintf(
                        'Ein Account mit der Email %s ist bereits registriert.',
                        $user->getEmail()
                    )
                )
            );

            return false;
        }

        $repeatedPassword = $form->get('repeat')->getData();

        if ($repeatedPassword != $user->getPassword()) {
            $form->get('repeat')->addError(new FormError('Die beiden Passwörter stimmen nicht überein.'));

            return false;
        }

        return true;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        if (!$this->em) {
            $this->em = $this->getDoctrine()->getManager();
        }

        return $this->em;
    }

    /**
     * @return UserRepository
     */
    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->getEntityManager()->getRepository(User::class);
        }

        return $this->repository;
    }
}