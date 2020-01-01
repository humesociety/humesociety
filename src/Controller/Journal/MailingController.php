<?php

namespace App\Controller\Journal;

use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for viewing the Hume Studies mailing list.
 *
 * @Route("/journal-manager/mailing", name="journal_mailing_")
 * @IsGranted("ROLE_EDITOR")
 */
class MailingController extends AbstractController
{
    /**
     * Route for showing mailing addresses.
     *
     * @param UserHandler $users The user handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(UserHandler $users): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'journal',
            'subarea' => 'mailing',
            'users' => $users->getMembersReceivingHumeStudies()
        ];

        // render and return the page
        return $this->render('journal/mailing/mailing.twig', $twigs);
    }
}
