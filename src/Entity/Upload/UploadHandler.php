<?php

namespace App\Entity\Upload;

use App\Entity\Conference\Conference;
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
     * Constructor function.
     *
     * @param ParameterBagInterface $params Symfony's paramater bag interface.
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
        return $this->getUploadsFromDirectory('images/');
    }

    /**
     * Get all years for which there are reports.
     *
     * @return int[]
     */
    public function getReportYears(): array
    {
        $paths = glob("{$this->uploadsDirectory}reports/*");
        return array_map(function ($path) {
            $bits = explode('/', $path);
            return intval($bits[count($bits) - 1]);
        }, $paths);
    }

    /**
     * Get all reports for a given year.
     *
     * @param int $year The year of the reports.
     * @return Upload[]
     */
    public function getReportsByYear(int $year): array
    {
        return $this->getUploadsFromDirectory("reports/{$year}/");
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
     * @param Conference $conference The conference whose files to get.
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
     * @return void
     */
    public function saveUpload(Upload $upload)
    {
        $upload->getFile()->move($this->uploadsDirectory.$upload->getPath(), $upload->getFilename());
    }

    /**
     * Save an image.
     *
     * @param Upload The image to save.
     * @return void
     */
    public function saveImage(Upload $upload)
    {
        $upload->setPath('images/');
        $this->saveUpload($upload);
    }

    /**
     * Save a report.
     *
     * @param Upload The report to save.
     * @param int $year The year of the report.
     * @return void
     */
    public function saveReport(Upload $upload, int $year)
    {
        $upload->setPath("reports/{$year}/");
        $this->saveUpload($upload);
    }

    /**
     * Delete an uploaded file from the disk.
     *
     * @param Upload The upload to delete.
     * @return void
     */
    public function deleteUpload(Upload $upload)
    {
        $fullPath = $this->uploadsDirectory.$upload->getPath().$upload->getFilename();
        if (file_exists($fullPath)) {
            $fs = new FileSystem();
            $fs->remove($fullPath);
        }
    }

    /**
     * Delete an image.
     *
     * @param string $filename The name of the image file.
     * @return void
     */
    public function deleteImage(string $filename)
    {
        $upload = new Upload('images/', $filename);
        $this->deleteUpload($upload);
    }

    /**
     * Delete a report.
     *
     * @param string $filename The name of the report.
     * @param string $year The year of the report.
     * @return void
     */
    public function deleteReport(string $filename, string $year)
    {
        $upload = new Upload("reports/{$year}/", $filename);
        $this->deleteUpload($upload);
    }

    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $path The path to the directory/file.
     * @return void
     */
    private function deletePath(string $path)
    {
        if (is_dir($path)) {
            $subPaths = glob($path.'*', GLOB_MARK);
            foreach ($subPaths as $subPath) {
                $this->deletePath($subPath);
            }
            rmdir($path);
        } elseif (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Move all uploads from one directory to another.
     *
     * @param string $oldPath The path to the old directory.
     * @param string $newPath The path to the new directory.
     * @return void
     */
    public function moveFiles($oldPath, $newPath)
    {
        // delete everything in the new path recursively, to make sure it's clear
        $this->deletePath($this->uploadsDirectory.$newPath);
        // then move everything from the old path to the new one
        rename($this->uploadsDirectory.$oldPath, $this->uploadsDirectory.$newPath);
    }
}
