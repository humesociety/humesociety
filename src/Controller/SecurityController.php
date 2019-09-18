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
 * @Route("/", name="security_")
 *
 * This controller contains functions for logging in and out, for registering, and for resetting
 * forgotten passwords.
 */
class SecurityController extends AbstractController
{
    /**
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

        return $this->render('site/security/login.twig', [
            'page' => ['id' => 'login', 'section' => 'security'],
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout() : Response
    {
        // nothing needs to happen here; Symfony takes care of it
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        UserHandler $userHandler
    ) : Response {
        $user = new User();
        $form = $this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);

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

        return $this->render('site/security/register.twig', [
            'page' => ['id' => 'register', 'section' => 'security'],
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/forgot", name="forgot")
     */
    public function forgot(
        Request $request,
        EmailHandler $emailHandler,
        UserHandler $userHandler
    ) : Response {
        $form = $this->createForm(UserForgotPasswordType::class);
        $form->handleRequest($request);

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

        return $this->render('site/security/forgot.twig', [
            'page' => ['id' => 'forgot', 'section' => 'security'],
            'userForgotPasswordForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset/{username}/{secret}", name="reset")
     */
    public function reset(
        string $username,
        string $secret,
        Request $request,
        UserHandler $userHandler,
        UserPasswordEncoderInterface $passwordEncoder
    ) : Response {
        $user = $userHandler->getUserByUsername($username);

        if (!$user) {
            throw $this->createNotFoundException('Page not found.');
        }
        if ($user->getPasswordResetSecret() != $secret || new \DateTime() > $user->getPasswordResetSecretExpires()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $form = $this->createForm(UserResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $encodedPassword = $passwordEncoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $userHandler->saveUser($user);
            $this->addFlash('success', 'Your password has been reset. You can now log in with your new password.');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('site/security/reset.twig', [
            'page' => ['id' => 'forgot', 'section' => 'security'],
            'user' => $user,
            'userResetPasswordForm' => $form->createView()
        ]);
    }
}
