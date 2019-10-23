<?php

namespace App\Controller\Admin\Society;

use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for viewing membership details.
 *
 * @Route("/admin/society/member", name="admin_society_member_")
 * @IsGranted("ROLE_EVPT")
 */
class MemberController extends AbstractController
{
    /**
     * Route for viewing members.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'member',
            'users' => $users->getMembers()
        ];

        // render and return the page
        return $this->render('admin/society/member/view.twig', $twigs);
    }

    /**
     * Route for viewing a member.
     *
     * @param UserHandler $users The user handler.
     * @param string $username The username of the user to view.
     * @return Response
     * @Route("/view/{username}", name="member")
     */
    public function member(UserHandler $users, string $username): Response
    {
        // look for the user
        $user = $users->getUserByUsername($username);

        // throw 404 error if the user isn't found
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // add additional twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'member',
            'user' => $user
        ];

        // render and return the page
        return $this->render('admin/society/member/member.twig', $twigs);
    }

    /**
     * Route for viewing membership statistics.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/statistics", name="statistics")
     */
    public function statistics(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'society',
            'subarea' => 'member',
            'users' => $users->getUsers()
        ];

        // render and return the page
        return $this->render('admin/society/member/statistics.twig', $twigs);
    }
}
