<?php
/**
 * AppBundle
 *
 * @namespace
 */

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Form\LoginForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\{
    Request, RedirectResponse
};
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\{
    Authentication\Token\TokenInterface,
    Encoder\UserPasswordEncoderInterface,
    Security,
    User\UserInterface,
    User\UserProviderInterface
};
use Symfony\Component\Security\{
    Guard\Authenticator\AbstractFormLoginAuthenticator,
    Http\Util\TargetPathTrait
};

/**
 * Class LoginFormAuthenticator
 *
 * This class is responsible for handling
 * the login form submission and
 * checking on each page if the user
 * is logged in with valid data.
 *
 * @package AppBundle\Security
 */
class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    /**
     * FormFactoryInterface used to create a instance of
     * the LoginForm to handle the request.
     *
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * EntityManagerInterface used to load
     * the user entity from the db.
     *
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * RouterInterface used to generate urls.
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * UserPasswordEncoderInterface used to check the
     * user credentials.
     *
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;

    /**
     * LoginFormAuthenticator constructor.
     *
     * @param FormFactoryInterface         $formFactory     FormFactoryInterface used to create a instance of
     *                                                      the LoginForm to handle the request.
     * @param EntityManagerInterface       $em              EntityManagerInterface used to load
     *                                                      the user entity from the db.
     * @param RouterInterface              $router          RouterInterface used to generate urls.
     * @param UserPasswordEncoderInterface $passwordEncoder UserPasswordEncoderInterface used to check the
     *                                                      user credentials.
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        RouterInterface $router,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->formFactory     = $formFactory;
        $this->em              = $em;
        $this->router          = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @inheritDoc
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request)
    {
        $isLoginSubmit = $request->attributes->get('_route') === 'security_login'
            && $request->isMethod(Request::METHOD_POST);

        if (!$isLoginSubmit) {
            // skip authentication
            return;
        }

        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);

        $data = $form->getData();
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        return $this->em->getRepository(User::class)->findOneBy(['email' => $username]);
    }

    /**
     * @inheritDoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['_password']);
    }

    use TargetPathTrait;

    /**
     * Handles redirection on successful authentication.
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $targetPath = $this->getTargetPath($request->getSession(), $providerKey);

        if (!$targetPath) {
            $targetPath = $this->router->generate('finance_index');
        }

        return new RedirectResponse($targetPath);
    }
}