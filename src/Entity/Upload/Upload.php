<?php

namespace App\Entity\Upload;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Upload objects represent files uploaded to the server. They are not persisted to the database.
 *
 * Uploads are either images, society reports, or files associated with a conference. They are
 * somewhat like articles and submissions, except that these latter entities have additional
 * metadata and are persisted to the database.
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
     * The full filename (including the extension).
     *
     * @var string|null
     */
    private $filename;

    /**
     * The file itself.
     *
     * This property is used temporarily when uploading the file.
     *
     * @var UploadedFile|null
     * @Assert\NotBlank(groups={"create"}, message="Please attach a file.")
     * @Assert\File(
     *     groups={"report"},
     *     mimeTypes = {"application/pdf"},
     *     mimeTypesMessage = "Please upload the report in PDF format."
     * )
     */
    private $file;

    /**
     * Constructor function.
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
     * Get the filename (potentially null when the object is first created).
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Set the filename.
     *
     * @param string The filename.
     * @return self
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get the name of the file (the filename minus the extension).
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
        return "/uploads/{$this->getPath()}{$this->getFilename()}";
    }

    /**
     * Get the subpath of the file (= the year for reports, the conference number for conference files).
     *
     * @return int|null
     */
    public function getSubPath(): ?int
    {
        $bits = explode('/', $this->path);
        if (array_key_exists(1, $bits) && intval($bits[1])) {
            return intval($bits[1]);
        }
        return null;
    }
}
