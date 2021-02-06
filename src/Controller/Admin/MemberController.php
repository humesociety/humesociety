<?php

namespace App\Controller\Admin;

use App\Entity\User\UserHandler;
use App\Entity\User\UserTypeAdmin;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for viewing membership details.
 *
 * @Route("/admin/member", name="admin_member_")
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
            'area' => 'member',
            'subarea' => 'member',
            'users' => $users->getMembers()
        ];

        // render and return the page
        return $this->render('admin/member/view.twig', $twigs);
    }

    /**
     * Route for editing a member's account.
     *
     * @param UserHandler $users The user handler.
     * @param string $username The username of the user to view.
     * @return Response
     * @Route("/edit/{username}", name="edit")
     */
    public function edit(Request $request, UserHandler $users, string $username): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'member',
            'subarea' => 'member'
        ];

        // look for the user
        $user = $users->getUserByUsername($username);

        // throw 404 error if the user isn't found
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // create and handle the contact details form
        $adminForm = $this->createForm(UserTypeAdmin::class, $user);
        $adminForm->handleRequest($request);
        if ($adminForm->isSubmitted() && $adminForm->isValid()) {
            $users->saveUser($this->getUser());
            $this->addFlash('notice', 'Account details for '.$user.' have been updated.');
            return $this->redirectToRoute('admin_member_index');
        }

        // add additional twig variables
        $twigs['user'] = $user;
        $twigs['adminForm'] = $adminForm->createView();

        // render and return the page
        return $this->render('admin/member/edit.twig', $twigs);
    }

    /**
     * Route for deleting a member's account.
     *
     * @param UserHandler $users The user handler.
     * @param string $username The username of the user to view.
     * @return Response
     * @Route("/delete/{username}", name="delete")
     */
    public function delete(Request $request, UserHandler $users, string $username): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'member',
            'subarea' => 'member'
        ];

        // look for the user
        $user = $users->getUserByUsername($username);

        // throw 404 error if the user isn't found
        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        // create and handle the delete page form
        $deleteForm = $this->createFormBuilder()->getForm();
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $users->deleteUser($user);
            $this->addFlash('notice', 'User "'.$user.'" has been deleted.');
            return $this->redirectToRoute('admin_member_index');
        }

        // add additional twig variables
        $twigs['user'] = $user;
        $twigs['deleteForm'] = $deleteForm->createView();

        // render and return the page
        return $this->render('admin/member/delete.twig', $twigs);
    }

    /**
     * Route for viewing membership statistics.
     *
     * @return Response
     * @Route("/statistics", name="statistics")
     */
    public function statistics(): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'member',
            'subarea' => 'statistics'
        ];

        // render and return the page
        return $this->render('admin/member/statistics.twig', $twigs);
    }
}
