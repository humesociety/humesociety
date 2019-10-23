<?php

namespace App\Controller\Admin\User;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing user data.
 *
 * @Route("/admin/user/account", name="admin_user_account_")
 * @IsGranted("ROLE_TECH")
 */
class AccountController extends AbstractController
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
            'area' => 'user',
            'subarea' => 'account',
            'users' => $users->getUsers()
        ];

        // render and return the page
        return $this->render('admin/user/account/view.twig', $twigs);
    }

    /**
     * Route for adding a role to a user.
     *
     * @param UserHandler $users The user handler.
     * @param User $user The user.
     * @param string The role to add.
     * @return JsonResponse
     * @Route("/role/{id}/add/{role}", name="add_role")
     */
    public function addRole(UserHandler $users, User $user, string $role): JsonResponse
    {
        $user->addRole($role);
        $users->saveUser($user);
        return $this->json(['success' => true]);
    }

    /**
     * Route for removing a role from a user.
     *
     * @param UserHandler $users The user handler.
     * @param User $user The user.
     * @param string The role to remove.
     * @return JsonResponse
     * @Route("/role/{id}/remove/{role}", name="remove_role")
     */
    public function removeRole(UserHandler $users, User $user, string $role): JsonResponse
    {
        $user->removeRole($role);
        $users->saveUser($user);
        return $this->json(['success' => true]);
    }

    /**
     * Route for viewing/editing user details.
     *
     * @param Request $request Symfony's request object.
     * @param UserHandler $users The user handler.
     * @param User $user The user.
     * @return Response
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Request $request, UserHandler $users, User $user): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'user',
            'subarea' => 'account',
            'user' => $user
        ];

        // render and return the page
        return $this->render('admin/user/account/edit.twig', $twigs);
    }
}
