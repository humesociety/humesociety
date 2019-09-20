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
     * @var string
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
     * @var UploadedFile
     * @Assert\NotBlank()
     */
    private $file;

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;
        $this->filename = $file->getClientOriginalName();

        return $this;
    }

    /**
     * Constructor function,
     *
     * @param string|null The subdirectory of the uploads folder where the file is stored.
     * @param string|null The full filename (including the extension).
     * @return void
     */
    public function __construct(?string $path = null, ?string $filename = null)
    {
        $this->path = $path;
        $this->filename = $filename;
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

    // Getters for derivative properties
    public function getUrl(): ?string
    {
        return '/uploads/'.$this->path.$this->filename;
    }

    public function getYear(): ?int
    {
        $bits = explode('/', $this->path);
        return $bits[1] ? intval($bits[1]) : null;
    }

    public function getName(): ?string
    {
        $bits = explode('.', $this->filename);
        array_pop($bits); // pop the file extension
        return implode('.', $bits);
    }
}
