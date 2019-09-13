<?php

namespace App\Entity\Upload;

use App\Entity\Article\Article;
use App\Entity\Note\Note;
use App\Entity\Conference\Conference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Most handlers relate to a repository, and handle data stored in the database. This upload handler
 * instead relates to the file system, and handles data stored in the uploads directory.
 */
class UploadHandler
{
    private $uploadsDirectory;
    private $imagesDirectory;
    private $reportsDirectory;
    private $conferencesDirectory;
    private $issuesDirectory;

    // Constructor function
    public function __construct(ParameterBagInterface $params)
    {
        $this->uploadsDirectory = $params->get('uploads_directory');
        $this->imagesDirectory = 'images/';
        $this->reportsDirectory = 'reports/';
        $this->conferencesDirectory = 'conferences/';
        $this->issuesDirectory = 'issues/';
    }

    // Getters
    public function getUploadsFromDirectory(string $directory): array
    {
        $paths = glob($this->uploadsDirectory.$directory.'*');

        return array_map(function ($path) use ($directory) {
            $filename = str_replace($this->uploadsDirectory.$directory, '', $path);
            return new Upload($directory, $filename);
        }, $paths);
    }

    public function getImages(): array
    {
        return $this->getUploadsFromDirectory($this->imagesDirectory);
    }

    public function getReportYears(): array
    {
        return array_map(function ($path) {
            $bits = explode('/', $path);
            return intval($bits[count($bits) - 1]);
        }, glob($this->uploadsDirectory.$this->reportsDirectory.'*'));
    }

    public function getReportsByYear(int $year): array
    {
        return $this->getUploadsFromDirectory($this->reportsDirectory.$year.'/');
    }

    public function getReports(): array
    {
        $reports = [];
        foreach ($this->getReportYears() as $year) {
            $reports = array_merge($reports, $this->getReportsByYear($year));
        }
        return $reports;
    }

    public function getConferenceUploads(Conference $conference): array
    {
        return $this->getUploadsFromDirectory($this->conferencesDirectory.$conference->getYear().'/');
    }

    // Save to disk
    public function saveUpload(Upload $upload, string $directory)
    {
        $upload->getFile()->move($this->uploadsDirectory.$directory, $upload->getFilename());
    }

    public function saveImage(Upload $upload)
    {
        $this->saveUpload($upload, $this->imagesDirectory);
    }

    public function saveReport(Upload $upload, int $year)
    {
        $this->saveUpload($upload, $this->reportsDirectory.$year.'/');
    }

    public function saveConferenceFile(Upload $upload, Conference $conference)
    {
        $this->saveUpload($upload, $this->conferencesDirectory.$conference->getYear().'/');
    }

    public function saveArticleFile(Article $article)
    {
        $issue = $article->getIssue();
        $directory = $this->issuesDirectory.'v'.$issue->getVolume().'n'.$issue->getNumber().'/';
        $article->getFile()->move($this->uploadsDirectory.$directory, $article->getFilename());
    }

    public function renameArticleFile(Article $article, string $oldFilename)
    {
        $issue = $article->getIssue();
        $directory = $this->uploadsDirectory.$this->issuesDirectory.'v'.$issue->getVolume().'n'.$issue->getNumber().'/';
        rename($directory.$oldFilename, $directory.$article->getFilename());
    }

    // Delete from disk
    public function deleteUpload(string $filename, string $directory)
    {
        if (file_exists($this->uploadsDirectory.$directory.$filename)) {
            $fs = new FileSystem();
            $fs->remove($this->uploadsDirectory.$directory.$filename);
        }
    }

    public function deleteImage(string $filename)
    {
        $this->deleteUpload($filename, $this->imagesDirectory);
    }

    public function deleteConferenceFile(string $filename, Conference $conference)
    {
        $this->deleteUpload($filename, $this->conferencesDirectory.$conference->getYear().'/');
    }

    public function deleteReport(string $filename, int $year)
    {
        $this->deleteUpload($filename, $this->reportsDirectory.$year.'/');
    }

    public function deleteArticleFile(Article $article)
    {
        $issue = $article->getIssue();
        $directory = $this->issuesDirectory.'v'.$issue->getVolume().'n'.$issue->getNumber().'/';
        $this->deleteUpload($article->getFilename(), $directory);
    }
}
