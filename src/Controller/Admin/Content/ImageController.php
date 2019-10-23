<?php

namespace App\Controller\Admin\Content;

use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadHandler;
use App\Entity\Upload\UploadType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for uploading and deleting images for displaying on the main web site.
 *
 * @Route("/admin/content/image", name="admin_content_image_")
 * @IsGranted("ROLE_EVPT")
 */
class ImageController extends AbstractController
{
    /**
     * Route for viewing images.
     *
     * @param Request $request Symfony's request object.
     * @param UploadHandler $uploads The upload handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(Request $request, UploadHandler $uploads): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'image',
            'images' => $uploads->getImages()
        ];

        // create and handle the upload form
        $upload = new Upload();
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);
        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploads->saveImage($upload);
            $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
        }

        // add additional twig variables
        $twigs['uploadForm'] = $uploadForm->createView();

        // render and return the page
        return $this->render('admin/content/image/view.twig', $twigs);
    }

    /**
     * Route for deleting an image.
     *
     * @param Request $request Symfony's request object.
     * @param UploadHandler $uploads The upload handler.
     * @param string $filename The filename of the image.
     * @return Response
     * @Route("/delete/{filename}", name="delete")
     */
    public function delete(Request $request, UploadHandler $uploads, string $filename): Response
    {
        // initialise twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'image',
            'filename' => $filename,
        ];

        // create and handle the upload form
        $uploadForm = $this->createFormBuilder()->getForm();
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploads->deleteImage($filename);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_content_image_index');
        }

        $twigs['uploadForm'] = $uploadForm->createView();

        // render and return the page
        return $this->render('admin/content/image/delete.twig', );
    }
}
