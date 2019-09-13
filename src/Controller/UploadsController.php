<?php

namespace App\Controller;

use App\Entity\Issue\IssueHandler;
use App\Entity\Upload\UploadHandler;
use App\Entity\User\UserHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/uploads", name="uploads_")
 */
class UploadsController extends AbstractController
{
    /**
     * @Route("/images/{filename}", name="page_image")
     */
    public function image(string $filename): Response
    {
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'images/'.$filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        return $response;
    }

    /**
     * @Route("/conferences/{year}/{file}", name="conference_file", requirements={"file"=".+"})
     */
    public function conferenceFile(string $year, string $file): Response
    {
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'conferences/'.$year.'/'.$file;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $response = new BinaryFileResponse($path);
        return $response;
    }

    /**
     * @Route("/reports/{year}/{filename}", name="report")
     * @IsGranted("ROLE_MEMBER")
     */
    public function report(string $year, string $filename): Response
    {
        if (!$this->getUser()->isMemberInGoodStanding()) {
            throw new AccessDeniedException();
        }

        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'reports/'.$year.'/'.$filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $response = new BinaryFileResponse($path);
        return $response;
    }

    /**
     * @Route("/issues/v{volume}n{number}/{filename}", name="article_file")
     */
    public function articleFile(int $volume, int $number, string $filename, IssueHandler $issueHandler): Response
    {
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'issues/v'.$volume.'n'.$number.'/'.$filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        $latestVolume = $issueHandler->getLatestVolume();
        if ($latestVolume && $latestVolume - $volume < 5) {
            if ($this->getUser() == null) {
                throw new AccessDeniedException('Please log in to view this article.');
            }
            if (!$this->getUser()->isMember()) {
                throw new AccessDeniedException('Please join the society to view this article.');
            }
            if (!$this->getUser()->isMemberInGoodStanding()) {
                throw new AccessDeniedException('Your membership has expires. Please renew to view this article.');
            }
        }

        $response = new BinaryFileResponse($path);
        return $response;
    }
}
