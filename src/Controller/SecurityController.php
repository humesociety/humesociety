<?php

namespace App\Controller;

use App\Entity\Email\EmailHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserRegistrationType;
use App\Entity\User\UserForgotPasswordType;
use App\Entity\User\UserResetPasswordType;
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
 * The controller for logging in and out, registering, and resetting forgotten passwords.
 *
 * @Route("/", name="security_")
 */
class SecurityController extends AbstractController
{
    /**
     * The page for logging in to the site.
     *
     * @param Request The Symfony HTTP request object.
     * @param AuthenticationUtils Symfony's authentication utilities.
     * @return Response
     * @Route("/login", name="login")
     */
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils
    ): Response {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // return the response
        return $this->render('site/security/login.twig', [
            'page' => ['id' => 'login', 'section' => 'security'],
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * The route for logging out.
     *
     * @return Response
     * @Route("/logout", name="logout")
     */
    public function logout() : Response
    {
        // nothing needs to happen here; Symfony takes care of it
    }

    /**
     * The page for creating an account on the site.
     *
     * @param Request The Symfony HTTP request object.
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
        UserHandler $userHandler
    ) : Response {
        // create the registration form
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

        // handle the registration form
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $userHandler->saveUser($user);
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        // return the response
        return $this->render('site/security/register.twig', [
            'page' => ['id' => 'register', 'section' => 'security'],
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * The page for requesting a forgotten username and password reset link.
     *
     * @param Request The Symfony HTTP request object.
     * @param EmailHandler The email handler.
     * @param UserHandler The user handler.
     * @return Response
     * @Route("/forgot", name="forgot")
     */
    public function forgot(
        Request $request,
        EmailHandler $emailHandler,
        UserHandler $userHandler
    ) : Response {
        // create the forgot details form
        $form = $this->createForm(UserForgotPasswordType::class);
        $form->handleRequest($request);

        // handle the forgot details form
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userHandler->getUserByEmail($email);
            if (!$user) {
                $form->get('email')->addError(new FormError('Email address not found'));
            } else {
                $user->setPasswordResetSecret();
                $userHandler->saveUser($user);
                $emailHandler->sendResetPasswordEmail($user);
                $this->addFlash('success', 'An email has been sent to '.$email.' with further instructions.');
            }
        }

        // return the response
        return $this->render('site/security/forgot.twig', [
            'page' => ['id' => 'forgot', 'section' => 'security'],
            'userForgotPasswordForm' => $form->createView()
        ]);
    }

    /**
     * The page for resetting a forgotten password.
     *
     * @param Request The Symfony HTTP request object.
     * @param UserHandler The user handler.
     * @param UserPasswordEncoderInterface Symfony's password encoder.
     * @param string The user's username.
     * @param string The user's reset password secret.
     * @return Response
     * @Route("/reset/{username}/{secret}", name="reset")
     */
    public function reset(
        Request $request,
        UserHandler $userHandler,
        UserPasswordEncoderInterface $passwordEncoder,
        string $username,
        string $secret
    ) : Response {
        // look for the user
        $user = $userHandler->getUserByUsername($username);
        if (!$user) {
            throw $this->createNotFoundException('Page not found.');
        }

        // check the reset password secret
        if ($user->getPasswordResetSecret() != $secret || new \DateTime() > $user->getPasswordResetSecretExpires()) {
            throw $this->createNotFoundException('Page not found.');
        }

        // create the reset password form
        $form = $this->createForm(UserResetPasswordType::class);
        $form->handleRequest($request);

        // handle the reset password form
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $userHandler->saveUser($user);
            $this->addFlash('success', 'Your password has been reset. You can now log in with your new password.');
            return $this->redirectToRoute('security_login');
        }

        // return the response
        return $this->render('site/security/reset.twig', [
            'page' => ['id' => 'forgot', 'section' => 'security'],
            'user' => $user,
            'userResetPasswordForm' => $form->createView()
        ]);
    }
}
