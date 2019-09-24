<?php

namespace App\Entity\Upload;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Upload objects represent files uploaded to the server. They are not persisted to the database.
 *
 * These files are tacitly one of three types (and stored in three separate subdirectories): images
 * for displaying within site pages (`uploads/images`), committee reports (`uploads\reports`), or
 * files associated with conferences (`uploads\conferences`).
 */
class Upload
{
    /**
     * The path to the file (i.e. the subdirectory of the uploads folder where the file is stored).
     *
     * @var string
     */
    private $path;

    /**
     * Get the path to the file (potentially null when the object is first created).
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Set the path to the file.
     *
     * @param string The path to the file.
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * The full filename (including the extension).
     *
     * @var string|null
     */
    private $filename;

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * The file itself.
     *
     * This property is used when uploading the file; once uploaded it is stored in the `uploads`
     * directory, rather than in the database.
     *
     * @var UploadedFile|null
     * @Assert\NotBlank(groups={"create"}, message="Please attach a file.")
     * @Assert\File(
     *     groups={"report"},
     *     mimeTypes = {"application/pdf"},
     *     mimeTypesMessage = "Please upload the report in PDF format."
     * )
     * @Assert\File(
     *     groups={"submission"},
     *     mimeTypes = {
     *          "application/msword",
     *          "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *          "application/rtf"
     *     },
     *     mimeTypesMessage = "Please upload your paper in Word or RTF format."
     * )
     */
    private $file;

    /**
     * Get the uploaded file.
     *
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Set the uploaded file.
     *
     * @param UploadedFile The uploaded file.
     * @return self
     */
    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;
        $this->filename = $file->getClientOriginalName();

        return $this;
    }

    /**
     * Get the URL for downloading the file.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return '/uploads/'.$this->getPath().$this->getFilename();
    }

    /**
     * Get the year of the file (conference files and reports).
     *
     * @return int|null
     */
    public function getYear(): ?int
    {
        $bits = explode('/', $this->path);
        if ($bits[1] && intval($bits[1])) {
          return intval($bits[1]);
        }
    }

    /**
     * Get the name of the file (minus the extension).
     *
     * @return string
     */
    public function getName(): string
    {
        $bits = explode('.', $this->filename);
        array_pop($bits); // pop the file extension
        return implode('.', $bits);
    }

    /**
     * Constructor function.
     *
     * @param string|null The subdirectory of the uploads folder where the file is stored.
     * @param string|null The full filename (including the extension).
     * @return void
     */
    public function __construct(?string $path = null, ?string $filename = null)
    {
        $this->path = $this->path || $path;
        $this->filename = $this->filename || $filename;
    }

    /**
     * ToString function.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->filename;
    }
}
