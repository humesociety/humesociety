<?php

namespace App\Controller;

use App\Entity\Issue\IssueHandler;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/data", name="data_")
 */
class DataController extends AbstractController
{
    /**
     * @Route("/members", name="members")
     * @IsGranted("ROLE_MEMBER")
     */
    public function members(SerializerInterface $serializer, UserHandler $userHandler): Response
    {
        if (!$this->getUser()->isMemberInGoodStanding()) {
            throw new AccessDeniedException();
        }
        $members = $userHandler->getMembersInGoodStanding();
        return new Response($serializer->serialize($members, 'json', ['groups' => 'json']));
    }

    /**
     * @Route("/user", name="user")
     */
    public function user(SerializerInterface $serializer): Response
    {
        return new Response($serializer->serialize($this->getUser(), 'json', ['groups' => 'json']));
    }

    /**
     * @Route("/users", name="users")
     * @IsGranted("ROLE_ADMIN")
     */
    public function users(SerializerInterface $serializer, UserHandler $userHandler): Response
    {
        $users = $userHandler->getUsers();
        return new Response($serializer->serialize($users, 'json', ['groups' => 'json']));
    }

    /**
     * @Route("/issues", name="issues")
     */
    public function issue(SerializerInterface $serializer, IssueHandler $issueHandler): Response
    {
        $issues = $issueHandler->getIssues();
        return new Response($serializer->serialize($issues, 'json', ['groups' => 'json']));
    }
}
