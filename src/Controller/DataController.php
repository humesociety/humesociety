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
     * Details of a user (the current user by default).
     *
     * @param SerializerInterface $serializer Symfony's serializer.
     * @param User|null $user The user to show
     * @return Response
     * @Route("/user/{user}", name="user")
     */
    public function user(SerializerInterface $serializer, ?User $user = null): Response
    {
        if ($user) {
            return new Response($serializer->serialize($user, 'json', ['groups' => 'json']));
        }
        return new Response($serializer->serialize($this->getUser(), 'json', ['groups' => 'json']));
    }

    /**
     * An array of users and their details.
     *
     * @param SerializerInterface $serializer Symfony's serializer.
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/users", name="users")
     * @IsGranted("ROLE_EVPT")
     */
    public function users(SerializerInterface $serializer, UserHandler $users): Response
    {
        return new Response($serializer->serialize($users->getUsers(), 'json', ['groups' => 'json']));
    }

    /**
     * An array of members and their details.
     *
     * @param SerializerInterface $serializer Symfony's serializer.
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/members", name="members")
     * @IsGranted("ROLE_MEMBER")
     */
    public function members(SerializerInterface $serializer, UserHandler $users): Response
    {
        if (!$this->getUser()->isMemberInGoodStanding()) {
            throw new AccessDeniedException();
        }
        $members = $users->getMembersInGoodStanding();
        return new Response($serializer->serialize($members, 'json', ['groups' => 'json']));
    }

    /**
     * An array of Hume Studies issues.
     *
     * @param SerializerInterface $serializer Symfony's serializer.
     * @param IssueHandler $issues The issue handler.
     * @return Response
     * @Route("/issues", name="issues")
     */
    public function issues(SerializerInterface $serializer, IssueHandler $issues): Response
    {
        return new Response($serializer->serialize($issues->getIssues(), 'json', ['groups' => 'json']));
    }

    /**
     * Details of the current Hume Conference.
     *
     * @param SerializerInterface $serializer Symfony's serializer.
     * @param ConferenceHandler $conferences The conference handler.
     * @return Response
     * @Route("/conference", name="conference")
     */
    public function conference(SerializerInterface $serializer, ConferenceHandler $conferences): Response
    {
        $conference = $conferences->getCurrentConference();
        if ($conference) {
            return new Response($serializer->serialize($conference, 'json', ['groups' => 'json']));
        }
        return new JsonResponse(null);
    }

    /**
     * The current Hume Conference keywords.
     *
     * @param SerializerInterface $serializer Symfony's serializer.
     * @param ConferenceHandler $conferences The conference handler.
     * @return Response
     * @Route("/conference/keywords", name="conference_keywords")
     */
    public function conferenceKeywords(SerializerInterface $serializer, ConferenceHandler $conferences): Response
    {
        $conference = $conferences->getCurrentConference();
        if ($conference) {
            return new JsonResponse($conferences->getSubmissionKeywords($conference));
        }
        return new JsonResponse(null);
    }
}
