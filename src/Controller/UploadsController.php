<?php

namespace App\Controller;

use App\Entity\Issue\IssueHandler;
use App\Entity\Submission\Submission;
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
 * The controller for retrieving uploaded files.
 *
 * @Route("/uploads", name="uploads_")
 */
class UploadsController extends AbstractController
{
    /**
     * Show uploaded images.
     *
     * @param string The filename of the image to show.
     * @return Response
     * @Route("/images/{filename}", name="page_image")
     */
    public function image(string $filename): Response
    {
        // look for the image
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'images/'.$filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
        return $response;
    }

    /**
     * Show uploaded conference files.
     *
     * @param string The year of the conference.
     * @param string The filename of the file to show.
     * @return Response
     * @Route("/conferences/{year}/{file}", name="conference_file", requirements={"file"=".+"})
     */
    public function conferenceFile(string $year, string $file): Response
    {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'conferences/'.$year.'/'.$file;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        return $response;
    }

    /**
     * Show uplaoded society reports.
     *
     * @param string The year of the report.
     * @param string The filename of the file to show.
     * @return Response
     * @Route("/reports/{year}/{filename}", name="report")
     * @IsGranted("ROLE_MEMBER")
     */
    public function report(string $year, string $filename): Response
    {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'reports/'.$year.'/'.$filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // check the user is allowed to see this report (restricted to members in good standing)
        if (!$this->getUser()->isMemberInGoodStanding()) {
            throw new AccessDeniedException();
        }

        // return the response
        $response = new BinaryFileResponse($path);
        return $response;
    }

    /**
     * Show uploaded Hume Studies articles.
     *
     * @param IssueHandler The issue handler.
     * @param int The Hume Studies volume.
     * @param int The Hume Stuides volume number.
     * @param string The article's filename.
     * @return Response
     * @Route("/issues/v{volume}n{number}/{filename}", name="article_file")
     */
    public function articleFile(
        IssueHandler $issueHandler,
        int $volume,
        int $number,
        string $filename
    ): Response {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= 'issues/v'.$volume.'n'.$number.'/'.$filename;
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // check the user is allowed to see the article
        $latestVolume = $issueHandler->getLatestVolume();
        if ($latestVolume && $latestVolume - $volume < 5) {
            if ($this->getUser() == null) {
                throw new AccessDeniedException('Please log in to view this article.');
            }
            if (!$this->getUser()->isMember()) {
                throw new AccessDeniedException('Please join the society to view this article.');
            }
            if (!$this->getUser()->isMemberInGoodStanding()) {
                throw new AccessDeniedException('Your membership has expired. Please renew to view this article.');
            }
        }

        // return the response
        $response = new BinaryFileResponse($path);
        return $response;
    }

    /**
     * Show conference submission file.
     *
     * @param Submission The submission to download.
     * @return Response
     * @Route("/submissions/{submission}", name="submission")
     */
    public function submission(Submission $submission): Response
    {
        // permissions check
        if (!$submission->userCanView($this->getUser())) {
            throw new AccessDeniedException('You do not have permission to download this file.');
        }

        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= $submission->getPath().$submission->getFilename();
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $submission->getFilename());
        return $response;
    }
}
