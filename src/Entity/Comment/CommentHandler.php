<?php

namespace App\Entity\Comment;

use App\Entity\Conference\Conference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The comment handler contains the main business logic for reading and writing comment data.
 */
class CommentHandler
{
    /**
     * The Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * The comment repository.
     *
     * @var CommentRepository
     */
    private $repository;

    /**
     * The uploads directory.
     *
     * @var string
     */
    private $uploadsDirectory;

    /**
     * Constructor function.
     *
     * @param EntityManagerInterface The Doctrine entity manager.
     * @param ParameterBagInterface Symfony's paramater bag interface.
     * @return void
     */
    public function __construct(EntityManagerInterface $manager, ParameterBagInterface $params)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository(Comment::class);
        $this->uploadsDirectory = $params->get('uploads_directory');
    }

    /**
     * Get all comment invitations.
     *
     * @param Conference|null Optional conference to restrict to.
     * @return Comment[]
     */
    public function getComments(?Conference $conference = null): array
    {
        $comments = $this->repository->findAll();
        if ($conference === null) {
            return $comments;
        }
        return array_filter($comments, function ($comment) use ($conference) {
            return $comment->getSubmission()->getConference() === $conference;
        });
    }

    /**
     * Save/update a comment.
     *
     * @param Comment The comment to save/update.
     */
    public function saveComment(Comment $comment)
    {
        if ($comment->getFile()) {
            $path = $this->uploadsDirectory.$comment->getPath();
            $comment->getFile()->move($path, $comment->getFilename());
            $comment->setFile(null);
        }
        $this->manager->persist($comment);
        $this->manager->flush();
    }

    /**
     * Delete a comment.
     *
     * @param Comment The comment to delete.
     */
    public function deleteComment(Comment $comment)
    {
        $fullpath = $this->uploadsDirectory.$comment->getPath().$comment->getFilename();
        if (file_exists($fullpath)) {
            $fs = new FileSystem();
            $fs->remove($fullpath);
        }
        $this->manager->remove($comment);
        $this->manager->flush();
    }
}
