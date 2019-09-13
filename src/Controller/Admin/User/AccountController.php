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
 * @Route("/admin/user/account", name="admin_user_account_")
 * @IsGranted("ROLE_TECH")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(UserHandler $userHandler): Response
    {
        return $this->redirectToRoute('admin_user_account_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(UserHandler $userHandler): Response
    {
        return $this->render('admin/user/account/view.twig', [
            'area' => 'user',
            'subarea' => 'account',
            'users' => $userHandler->getUsers()
        ]);
    }

    /**
     * @Route("/role/{id}/add/{role}", name="add_role")
     */
    public function addRole(User $user, string $role, UserHandler $userHandler, Request $request): Response
    {
        $user->addRole($role);
        $userHandler->saveUser($user);
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/role/{id}/remove/{role}", name="remove_role")
     */
    public function removeRole(User $user, string $role, UserHandler $userHandler, Request $request): Response
    {
        $user->removeRole($role);
        $userHandler->saveUser($user);
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(User $user, UserHandler $userHandler, Request $request): Response
    {
        return $this->render('admin/user/account/edit.twig', [
            'area' => 'user',
            'subarea' => 'account',
            'user' => $user
        ]);
    }
}
