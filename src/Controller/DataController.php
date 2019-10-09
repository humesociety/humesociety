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
     * @param SerializerInterface Symfony's serializer.
     * @return Response
     * @Route("/user/{user}", name="user")
     */
    public function currentUser(?User $user = null, SerializerInterface $serializer): Response
    {
        if ($user) {
            return new Response($serializer->serialize($user, 'json', ['groups' => 'json']));
        }
        return new Response($serializer->serialize($this->getUser(), 'json', ['groups' => 'json']));
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
    public function users(SerializerInterface $serializer, UserHandler $users): Response
    {
        return new Response($serializer->serialize($users->getUsers(), 'json', ['groups' => 'json']));
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
     * @param SerializerInterface Symfony's serializer.
     * @param IssueHandler The issue handler.
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
     * @param SerializerInterface Symfony's serializer.
     * @param ConferenceHandler The conference handler.
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
     * @param SerializerInterface Symfony's serializer.
     * @param ConferenceHandler The conference handler.
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
