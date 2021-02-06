<?php

namespace App\Controller\Conference;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use App\Entity\User\UserInvitedType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing active users.
 *
 * @Route("/conference-manager/user", name="conference_user_")
 * @IsGranted("ROLE_ORGANISER")
 */
class UserController extends AbstractController
{
    /**
     * Route for viewing/activating any user.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'all',
            'users' => $users->getUsers()
        ];

        // render and return the page
        return $this->render('conference/user/index.twig', $twigs);
    }

    /**
     * Route for creating an invited user.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/create", name="create")
     */
    public function create(UserHandler $users, Request $request): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'all'
        ];

        // create and handle the form for creating an invited user
        $user = new User();
        $reviewerForm = $this->createForm(UserInvitedType::class, $user);
        $reviewerForm->handleRequest($request);
        if ($reviewerForm->isSubmitted() && $reviewerForm->isValid()) {
            // set a username and password - these cannot be blank, but won't do anything, since
            // invited users cannot log in
            $user->setUsername($user->getEmail());
            $user->setPassword('password');
            $existing = $users->getUserByEmail($user->getEmail());
            if ($existing) {
                $error = new FormError('There is already a user with this email address in the database.');
                $reviewerForm->get('email')->addError($error);
            } else {
                $users->saveUser($user);
                $this->addFlash('notice', "A record has been created for {$user}.");
                // redirect to the index page
                return $this->redirectToRoute('conference_user_index');
            }
        }

        // add additional twig variables
        $twigs['reviewerForm'] = $reviewerForm->createView();

        // render and return the page
        return $this->render('conference/user/create.twig', $twigs);
    }

    /**
     * Route for viewing/activating volunteer reviewers.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/reviewer", name="reviewer")
     */
    public function reviewer(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'reviewer',
            'users' => $users->getReviewVolunteers()
        ];

        // render and return the page
        return $this->render('conference/user/reviewer.twig', $twigs);
    }

    /**
     * Route for viewing/activating volunteer commentators.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/commentator", name="commentator")
     */
    public function commentator(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'commentator',
            'users' => $users->getCommentVolunteers()
        ];

        // render and return the page
        return $this->render('conference/user/commentator.twig', $twigs);
    }

    /**
     * Route for viewing/activating volunteer chairs.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/chair", name="chair")
     */
    public function chair(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'chair',
            'users' => $users->getChairVolunteers()
        ];

        // render and return the page
        return $this->render('conference/user/chair.twig', $twigs);
    }

    /**
     * Route for toggling whether a user is active.
     *
     * @param UserHandler $users The user handler.
     * @param User $user The user to toggle.
     * @return JsonResponse
     * @Route("/toggle/{user}", name="toggle")
     */
    public function toggle(UserHandler $users, User $user): JsonResponse
    {
        // toggle the user's active status
        $user->setActive(!$user->isActive());
        $users->saveUser($user);

        // return a json response
        return $this->json(['success' => true]);
    }
}
