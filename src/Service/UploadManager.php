<?php

namespace App\Service;

use App\Entity\Conference\Conference;
use App\Entity\Upload\Upload;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * The uploads manager contains the main business logic for reading and writing uploaded files to disk.
 */
class UploadManager
{
    /**
     * The path the the uploads directory.
     */
    private $uploadsDirectory;

    /**
     * Constructor function.
     *
     * @param ParamaterBagInterface Symfony's paramater bag interface.
     * @return void
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    /**
     * Get all uploads from a subdirectory of the uploads directory.
     *
     * @param string The path to the subdirectory.
     * @return Upload[]
     */
    private function getUploadsFromDirectory(string $directory): array
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
        return $this->getUploadsFromDirectory('images/');
    }

    /**
     * Get all years for which there are reports.
     *
     * @return int[]
     */
    public function getReportYears(): array
    {
        $paths = glob($this->uploadsDirectory.'reports/*');
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
        return $this->getUploadsFromDirectory('reports/'.$year.'/');
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
        return $this->getUploadsFromDirectory($conference->getPath());
    }

    /**
     * Save an upload to disk.
     *
     * @param Upload The upload to save.
     */
    public function saveUpload(Upload $upload)
    {
        $upload->getFile()->move($this->uploadsDirectory.$upload->getPath(), $upload->getFilename());
    }

    /**
     * Delete an uploaded file from the disk.
     *
     * @param Upload The uploaded file to delete.
     */
    public function deleteUpload(Upload $upload)
    {
        $fullpath = $this->uploadsDirectory.$upload->getPath().$upload->getFilename();
        if (file_exists($fullpath)) {
            $fs = new FileSystem();
            $fs->remove($fullpath);
        }
    }
}
