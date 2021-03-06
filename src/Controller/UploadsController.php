<?php

namespace App\Controller;

use App\Entity\Comment\Comment;
use App\Entity\Issue\IssueHandler;
use App\Entity\Paper\Paper;
use App\Entity\Submission\Submission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Controller for retrieving uploaded files.
 *
 * @Route("/uploads", name="uploads_")
 */
class UploadsController extends AbstractController
{
    /**
     * Show uploaded images.
     *
     * @param string $filename The filename of the image to show.
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
     * @param string $year The year of the conference.
     * @param string $file The path of the file to show.
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
     * @param string $year The year of the report.
     * @param string $filename The filename of the file to show.
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
     * @param IssueHandler $issues The issue handler.
     * @param int $volume The Hume Studies volume.
     * @param int $number The Hume Studies volume number.
     * @param string $filename The article's filename.
     * @return Response
     * @Route("/issues/v{volume}n{number}/{filename}", name="article_file")
     */
    public function articleFile(
        IssueHandler $issues,
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
        $latestVolume = $issues->getLatestVolume();
        if ($latestVolume && $latestVolume - $volume < 5) {
            if ($this->getUser() === null) {
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
     * @param Submission $submission The submission to download.
     * @param string|null $secret A secret to allow downloading the file.
     * @return Response
     * @Route("/submissions/{submission}/{secret}", name="submission")
     */
    public function submission(Submission $submission, ?string $secret = null): Response
    {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= $submission->getPath().$submission->getFilename();
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // check the user is allowed to view the file
        if (!$submission->userCanView($this->getUser(), $secret)) {
            throw new AccessDeniedException('You do not have permission to download this file.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $submission->getFilename());
        return $response;
    }

    /**
     * Show conference submission file FINAL version.
     *
     * @param Submission $submission The submission to download.
     * @return Response
     * @Route("/final-submissions/{submission}", name="final_submission")
     */
    public function finalSubmission(Submission $submission): Response
    {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= $submission->getPath().'final/'.$submission->getFinalFilename();
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $submission->getFinalFilename());
        return $response;
    }

    /**
     * Show conference submission comment file.
     *
     * @param Comment $comment The submission to download.
     * @return Response
     * @Route("/comment/{comment}", name="comment")
     */
    public function comment(Comment $comment): Response
    {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= $comment->getPath().$comment->getFilename();
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $comment->getFilename());
        return $response;
    }

    /**
     * Show invited paper file.
     *
     * @param Paper $paper The invited paper to download.
     * @return Response
     * @Route("/paper/{paper}", name="paper")
     */
    public function paper(Paper $paper): Response
    {
        // look for the file
        $path = $this->container->get('parameter_bag')->get('uploads_directory');
        $path .= $paper->getPath().$paper->getFilename();
        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        // return the response
        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $paper->getFilename());
        return $response;
    }
}
