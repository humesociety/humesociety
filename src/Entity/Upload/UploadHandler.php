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
     * Constructor function.
     *
     * @param ParamaterBagInterface Symfony's paramater bag interface.
     * @return void
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDirectory = $params->get('uploads_directory');
        $this->imagesDirectory = 'images/';
        $this->reportsDirectory = 'reports/';
        $this->conferencesDirectory = 'conferences/';
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
        $paths = glob($this->uploadsDirectory.$this->reportsDirectory.'*');

        return array_map(function ($path) {
            $bits = explode('/', $path);
            return intval($bits[count($bits) - 1]);
        }, $paths);
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
        return $this->getUploadsFromDirectory($this->conferencesDirectory.$conference->getNumber().'/');
    }

    /**
     * Save an upload to disk.
     *
     * @param Upload The upload to save.
     */
    private function saveUpload(Upload $upload)
    {
        $upload->getFile()->move($this->uploadsDirectory.$upload->getPath(), $upload->getFilename());
    }

    /**
     * Save an image.
     *
     * @param Upload The image to save.
     */
    public function saveImage(Upload $upload)
    {
        $upload->setPath($this->imagesDirectory);
        $this->saveUpload($upload);
    }

    /**
     * Save a report.
     *
     * @param Upload The report to save.
     */
    public function saveReport(Upload $upload, int $year)
    {
        $upload->setPath($this->reportsDirectory.$year.'/');
        $this->saveUpload($upload);
    }

    /**
     * Save a conference file.
     *
     * @param Upload The conference file to save.
     */
    public function saveConferenceFile(Upload $upload, Conference $conference)
    {
        $upload->setPath($conference->getPath());
        $this->saveUpload($upload);
    }

    /**
     * Delete an uploaded file from the disk.
     *
     * @param Upload The uploaded file to delete.
     */
    private function deleteUpload(Upload $upload)
    {
        $fullpath = $this->uploadsDirectory.$upload->getPath().$upload->getFilename();
        if (file_exists($fullpath)) {
            $fs = new FileSystem();
            $fs->remove($fullpath);
        }
    }

    /**
     * Delete an image.
     *
     * @param string The name of the image file.
     */
    public function deleteImage(string $filename)
    {
        $upload = new Upload($this->imagesDirectory, $filename);
        $this->deleteUpload($upload);
    }

    /**
     * Delete a conference file.
     *
     * @param string The name of the conference file.
     * @param Conference The conference.
     */
    public function deleteConferenceFile(string $filename, Conference $conference)
    {
        $upload = new Upload($conference->getPath(), $filename);
        $this->deleteUpload($upload);
    }

    /**
     * Delete a report.
     *
     * @param string The name of the report.
     * @param int The year of the report.
     */
    public function deleteReport(string $filename, string $year)
    {
        $upload = new Upload($this->reportsDirectory.$year.'/', $filename);
        $this->deleteUpload($upload);
    }
}
