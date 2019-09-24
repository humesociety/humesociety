<?php

namespace App\Entity\Upload;

use App\Entity\Article\Article;
use App\Entity\Note\Note;
use App\Entity\Conference\Conference;
use App\Entity\Review\Review;
use App\Entity\Submission\Submission;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The uploads handler contains the main business logic for reading and writing uploaded files to
 * disk.
 *
 * Most handlers relate to a repository, and handle data stored in the database. This upload handler
 * instead relates to the file system, and handles data stored in the uploads directory.
 */
class UploadHandler
{
    /**
     * The path the the uploads directory.
     */
    private $uploadsDirectory;

    /**
     * The images subdirectory.
     */
    private $imagesDirectory;

    /**
     * The reports subdirectory.
     */
    private $reportsDirectory;

    /**
     * The conferences subdirectory.
     */
    private $conferencesDirectory;

    /**
     * The journal issues subdirectory.
     */
    private $issuesDirectory;

    /**
     * The conference submissions subdirectory.
     */
    private $submissionsDirectory;

    /**
     * The conference submission reviews subdirectory.
     */
    private $reviewsDirectory;

    // Constructor function
    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDirectory = $params->get('uploads_directory');
        $this->imagesDirectory = 'images/';
        $this->reportsDirectory = 'reports/';
        $this->conferencesDirectory = 'conferences/';
        $this->issuesDirectory = 'issues/';
        $this->submissionsDirectory = 'submissions/';
        $this->reviewsDirectory = 'reviews/';
    }

    /**
     * Get all uploads from a subdirectory of the uploads directory.
     *
     * @param string The path to the subdirectory.
     * @return Upload[]
     */
    public function getUploadsFromDirectory(string $directory): array
    {
        $paths = glob($this->uploadsDirectory.$directory.'*');

        return array_map(function ($path) use ($directory) {
            $filename = str_replace($this->uploadsDirectory.$directory, '', $path);
            return new Upload($directory, $filename);
        }, $paths);
    }

    /**
     * Get all image uploads.
     *
     * @return Upload[]
     */
    public function getImages(): array
    {
        return $this->getUploadsFromDirectory($this->imagesDirectory);
    }

    /**
     * Get all years for which there are reports.
     *
     * @return int[]
     */
    public function getReportYears(): array
    {
        return array_map(function ($path) {
            $bits = explode('/', $path);
            return intval($bits[count($bits) - 1]);
        }, glob($this->uploadsDirectory.$this->reportsDirectory.'*'));
    }

    /**
     * Get all reports for a given year.
     *
     * @param int The year of the reports.
     * @return Upload[]
     */
    public function getReportsByYear(int $year): array
    {
        return $this->getUploadsFromDirectory($this->reportsDirectory.$year.'/');
    }

    /**
     * Get all reports (for all years).
     *
     * @return Upload[]
     */
    public function getReports(): array
    {
        $reports = [];
        foreach ($this->getReportYears() as $year) {
            $reports = array_merge($reports, $this->getReportsByYear($year));
        }
        return $reports;
    }

    /**
     * Get all uploads for a given conference.
     *
     * @param Conference The conference whose files to get.
     * @return Upload[]
     */
    public function getConferenceUploads(Conference $conference): array
    {
        return $this->getUploadsFromDirectory($this->conferencesDirectory.$conference->getYear().'/');
    }

    /**
     * Save an upload to disk.
     *
     * @param Upload The upload to save.
     * @param string The subdirectory of the uploads directory to save it in.
     */
    public function saveUpload(Upload $upload, string $directory)
    {
        $upload->getFile()->move($this->uploadsDirectory.$directory, $upload->getFilename());
    }

    /**
     * Save an image.
     *
     * @param Upload The image to save.
     */
    public function saveImage(Upload $upload)
    {
        $this->saveUpload($upload, $this->imagesDirectory);
    }

    /**
     * Save a report.
     *
     * @param Upload The report to save.
     */
    public function saveReport(Upload $upload, int $year)
    {
        $this->saveUpload($upload, $this->reportsDirectory.$year.'/');
    }

    /**
     * Save a conference file.
     *
     * @param Upload The conference file to save.
     */
    public function saveConferenceFile(Upload $upload, Conference $conference)
    {
        $this->saveUpload($upload, $this->conferencesDirectory.$conference->getYear().'/');
    }

    /**
     * Save a journal article file.
     *
     * @param Article The article whose file to save.
     */
    public function saveArticleFile(Article $article)
    {
        $issue = $article->getIssue();
        $directory = $this->issuesDirectory.'v'.$issue->getVolume().'n'.$issue->getNumber().'/';
        $article->getFile()->move($this->uploadsDirectory.$directory, $article->getFilename());
    }

    /**
     * Rename an article file.
     *
     * @param Article The article file to rename (with its new name set).
     * @param string The old filename.
     */
    public function renameArticleFile(Article $article, string $oldFilename)
    {
        $issue = $article->getIssue();
        $directory = $this->uploadsDirectory.$this->issuesDirectory.'v'.$issue->getVolume().'n'.$issue->getNumber().'/';
        rename($directory.$oldFilename, $directory.$article->getFilename());
    }

    /**
     * Save a conference submission file.
     *
     * @param Submission The submission whose file to save.
     */
    public function saveSubmissionFile(Submission $submission)
    {
        $submission->getFile()->move($this->uploadsDirectory.$submission->getPath(), $submission->getFilename());
    }

    /**
     * Save a conference submission review file.
     *
     * @param Review The review whose file to save.
     */
    public function saveReviewFile(Review $review)
    {
        $review->getFile()->move($this->uploadsDirectory.$review->getPath(), $review->getFilename());
    }

    /**
     * Delete an uploaded file from the disk.
     *
     * @param string The name of the file.
     * @param string The name of the subdirectory where the file is.
     */
    public function deleteUpload(string $filename, string $directory)
    {
        if (file_exists($this->uploadsDirectory.$directory.$filename)) {
            $fs = new FileSystem();
            $fs->remove($this->uploadsDirectory.$directory.$filename);
        }
    }

    /**
     * Delete an image.
     *
     * @param string The name of the image file.
     */
    public function deleteImage(string $filename)
    {
        $this->deleteUpload($filename, $this->imagesDirectory);
    }

    /**
     * Delete a conference file.
     *
     * @param string The name of the conference file.
     * @param Conference The conference.
     */
    public function deleteConferenceFile(string $filename, Conference $conference)
    {
        $this->deleteUpload($filename, $this->conferencesDirectory.$conference->getYear().'/');
    }

    /**
     * Delete a report.
     *
     * @param string The name of the report.
     * @param int The year of the report.
     */
    public function deleteReport(string $filename, int $year)
    {
        $this->deleteUpload($filename, $this->reportsDirectory.$year.'/');
    }

    /**
     * Delete an article file.
     *
     * @param Article The article whose file to delete.
     */
    public function deleteArticleFile(Article $article)
    {
        $issue = $article->getIssue();
        $directory = $this->issuesDirectory.'v'.$issue->getVolume().'n'.$issue->getNumber().'/';
        $this->deleteUpload($article->getFilename(), $directory);
    }

    /**
     * Delete a conference submission file.
     *
     * @param Submission The submission whose file to delete.
     */
    public function deleteSubmissionFile(Submission $submission)
    {
        $this->deleteUpload($submission->getFilename(), $submission->getPath());
    }

    /**
     * Delete a conference submission review file.
     *
     * @param Review The review whose file to delete.
     */
    public function deleteReviewFile(Review $review)
    {
        $this->deleteUpload($review->getFilename(), $review->getPath());
    }
}
