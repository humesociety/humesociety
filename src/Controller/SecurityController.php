<?php

namespace App\Controller;

use App\Entity\Email\EmailHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserTypeRegistration;
use App\Entity\User\UserTypeForgotCredentials;
use App\Entity\User\UserTypeResetPassword;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller for logging in and out, registering, and resetting forgotten passwords.
 *
 * @Route("/", name="security_")
 */
class SecurityController extends AbstractController
{
    /**
     * Route for logging in to the site.
     *
     * @param Request Symfony's request object.
     * @param AuthenticationUtils Symfony's authentication utilities.
     * @return Response
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'login', 'section' => 'security', 'title' => 'Sign In'],
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ];

        // render and return the page
        return $this->render('site/security/login.twig', $twigs);
    }

    /**
     * Route for logging out.
     *
     * @return Response
     * @Route("/logout", name="logout")
     */
    public function logout() : Response
    {
        // nothing needs to happen here; Symfony takes care of it
    }

    /**
     * Route for creating an account on the site.
     *
     * @param Request Symfony's request object.
     * @param UserPasswordEncoderInterface Symfony's password encoder.
     * @param GuardAuthenticationHandler Symfony's authentication handler.
     * @param LoginFormAuthenticator The login form authenticator.
     * @param UserHandler The user handler.
     * @return Response
     * @Route("/register", name="register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        UserHandler $users
    ) : Response {
        // initialise the twig variables
        $twigs = ['page' => ['slug' => 'register', 'section' => 'security', 'title' => 'Sign Up']];

        // create and handle the registration form
        $user = new User();
        $registrationForm = $this->createForm(UserTypeRegistration::class, $user);
        $registrationForm->handleRequest($request);
        if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $users->saveUser($user);
            return $guardHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'main');
        }

        // add additional twig variables
        $twigs['registrationForm'] = $registrationForm->createView();

        // return the response
        return $this->render('site/security/register.twig', $twigs);
    }

    /**
     * Route for requesting a forgotten username and password reset link.
     *
     * @param Request Symfony's request object.
     * @param EmailHandler The email handler.
     * @param UserHandler The user handler.
     * @return Response
     * @Route("/forgot", name="forgot")
     */
    public function forgot(Request $request, EmailHandler $emails, UserHandler $users) : Response
    {
        // initialise the twig variables
        $twigs = ['page' => ['slug' => 'forgot', 'section' => 'security', 'title' => 'Forgot Credentials']];

        // create and handle the forgot details form
        $forgotCredentialsForm = $this->createForm(UserTypeForgotCredentials::class);
        $forgotCredentialsForm->handleRequest($request);
        if ($forgotCredentialsForm->isSubmitted() && $forgotCredentialsForm->isValid()) {
            $email = $forgotCredentialsForm->get('email')->getData();
            $user = $users->getUserByEmail($email);
            if (!$user) {
                $forgotCredentialsForm->get('email')->addError(new FormError('Email address not found.'));
            } else {
                $user->setPasswordResetSecret();
                $users->saveUser($user);
                $emails->sendForgotCredentialsEmail($user);
                $this->addFlash('success', 'An email has been sent to '.$email.' with further instructions.');
            }
        }

        // add additional twig variables
        $twigs['forgotCredentialsForm'] = $forgotCredentialsForm->createView();

        // return the response
        return $this->render('site/security/forgot.twig', $twigs);
    }

    /**
     * Route for resetting a forgotten password.
     *
     * @param Request Symfony's request object.
     * @param UserPasswordEncoderInterface Symfony's password encoder.
     * @param UserHandler The user handler.
     * @param string The user's username.
     * @param string The user's reset password secret.
     * @return Response
     * @Route("/reset/{username}/{secret}", name="reset")
     */
    public function reset(
        Request $request,
        UserHandler $users,
        UserPasswordEncoderInterface $passwordEncoder,
        string $username,
        string $secret
    ) : Response {
        // look for the user
        $user = $users->getUserByUsername($username);

        // throw 404 error if the user isn't found
        if (!$user) {
            throw $this->createNotFoundException('Page not found.');
        }

        // throw 404 error if the secret is wrong
        if ($user->getPasswordResetSecret() !== $secret) {
            throw $this->createNotFoundException('Page not found.');
        }

        // throw 404 error if the secret has expired
        if (new \DateTime() > $user->getPasswordResetSecretExpires()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create and handle the reset password form
        $resetPasswordForm = $this->createForm(UserTypeResetPassword::class);
        $resetPasswordForm->handleRequest($request);
        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
            $plainPassword = $resetPasswordForm->get('password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $users->saveUser($user);
            $this->addFlash('success', 'Your password has been reset. You can now log in with your new password.');
            return $this->redirectToRoute('security_login');
        }

        // initialise the twig variables
        $twigs = [
            'page' => ['slug' => 'reset', 'section' => 'security', 'title' => "Reset Password for {$user}"],
            'user' => $user,
            'resetPasswordForm' => $resetPasswordForm->createView()
        ];

        // render and return the page
        return $this->render('site/security/reset.twig', $twigs);
    }
}
