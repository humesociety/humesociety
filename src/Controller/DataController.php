<?php

namespace App\Controller;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Issue\IssueHandler;
use App\Entity\User\User;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * The controller for returning JSON data from the database.
 *
 * @Route("/data", name="data_")
 */
class DataController extends AbstractController
{
    /**
     * Details of the current user.
     *
     * @param SerializerInterface Symfony's serializer.
     * @return Response
     * @Route("/user", name="user")
     */
    public function currentUser(SerializerInterface $serializer): Response
    {
        return new Response($serializer->serialize($this->getUser(), 'json', ['groups' => 'json']));
    }

    /**
     * Details of a specific user.
     *
     * @param User The user.
     * @param SerializerInterface Symfony's serializer.
     * @return Response
     * @Route("/user/{user}", name="user")
     * @IsGranted("ROLE_ADMIN")
     */
    public function specifiedUser(User $user, SerializerInterface $serializer): Response
    {
        return new Response($serializer->serialize($user, 'json', ['groups' => 'json']));
    }

    /**
     * An array of users and their details.
     *
     * @param SerializerInterface Symfony's serializer.
     * @param UserHandler The user handler.
     * @return Response
     * @Route("/users", name="users")
     * @IsGranted("ROLE_ADMIN")
     */
    public function users(SerializerInterface $serializer, UserHandler $userHandler): Response
    {
        $users = $userHandler->getUsers();
        return new Response($serializer->serialize($users, 'json', ['groups' => 'json']));
    }

    /**
     * An array of members and their details.
     *
     * @param SerializerInterface Symfony's serializer.
     * @param UserHandler The user handler.
     * @return Response
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
     * An array of Hume Studies issues.
     *
     * @param SerializerInterface Symfony's serializer.
     * @param IssueHandler The issue handler.
     * @return Response
     * @Route("/issues", name="issues")
     */
    public function issues(SerializerInterface $serializer, IssueHandler $issueHandler): Response
    {
        $issues = $issueHandler->getIssues();
        return new Response($serializer->serialize($issues, 'json', ['groups' => 'json']));
    }

    /**
     * Details of the current Hume Conference.
     *
     * @param SerializerInterface Symfony's serializer.
     * @param ConferenceHandler The conference handler.
     * @return Response
     * @Route("/conference", name="conference")
     */
    public function conference(
        SerializerInterface $serializer,
        ConferenceHandler $conferenceHandler
    ): Response {
        $conference = $conferenceHandler->getCurrentConference();
        if ($conference) {
            return new Response($serializer->serialize($conference, 'json', ['groups' => 'json']));
        }
        return new JsonResponse(null);
    }

    /**
     * The current Hume Conference keywords.
     *
     * @param SerializerInterface Symfony's serializer.
     * @param ConferenceHandler The conference handler.
     * @return Response
     * @Route("/conference/keywords", name="conference_keywords")
     */
    public function conferenceKeywords(
        SerializerInterface $serializer,
        ConferenceHandler $conferenceHandler
    ): Response {
        $conference = $conferenceHandler->getCurrentConference();
        if ($conference) {
            return new JsonResponse($conferenceHandler->getSubmissionKeywords($conference));
        }
        return new JsonResponse(null);
    }
}
