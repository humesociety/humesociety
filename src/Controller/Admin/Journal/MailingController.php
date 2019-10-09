<?php

namespace App\Controller\Admin\Journal;

use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for viewing the Hume Studies mailing list.
 *
 * @Route("/admin/journal/mailing", name="admin_journal_mailing_")
 * @IsGranted("ROLE_EDITOR")
 */
class MailingController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(UserHandler $userHandler): Response
    {
        return $this->render('admin/journal/mailing/mailing.twig', [
            'area' => 'journal',
            'subarea' => 'mailing',
            'users' => $userHandler->getMembersReceivingHumeStudies()
        ]);
    }
}
