<?php

namespace App\Controller\Admin\Conference;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing active users.
 *
 * @Route("/admin/conference/user", name="admin_conference_user_")
 * @IsGranted("ROLE_ORGANISER")
 */
class UserController extends AbstractController
{
    /**
     * Route for viewing all users.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'user',
            'title' => 'Active Users',
            'users' => $users->getUsers()
        ];

        // render and return the page
        return $this->render('admin/conference/user/index.twig', $twigs);
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
