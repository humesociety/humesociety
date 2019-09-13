<?php

namespace App\Controller\Admin\Society;

use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/society/member", name="admin_society_member_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for viewing membership details.
 */
class MemberController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_society_member_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(UserHandler $userHandler): Response
    {
        return $this->render('admin/society/member/view.twig', [
            'area' => 'society',
            'subarea' => 'member',
            'users' => $userHandler->getMembers()
        ]);
    }

    /**
     * @Route("/view/{username}", name="member")
     */
    public function member(string $username, UserHandler $userHandler): Response
    {
        $user = $userHandler->getUserByUsername($username);
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }
        return $this->render('admin/society/member/member.twig', [
            'area' => 'society',
            'subarea' => 'member',
            'user' => $user
        ]);
    }

    /**
     * @Route("/statistics", name="statistics")
     */
    public function statistics(UserHandler $userHandler): Response
    {
        return $this->render('admin/society/member/statistics.twig', [
            'area' => 'society',
            'subarea' => 'member',
            'users' => $userHandler->getUsers()
        ]);
    }
}
