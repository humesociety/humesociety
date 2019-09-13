<?php

namespace App\Controller\Admin\Content;

use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadHandler;
use App\Entity\Upload\UploadType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/content/image", name="admin_content_image_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for uploading and deleting images for displaying on the main web site.
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_content_image_view');
    }

    /**
     * @Route("/view", name="view")
     */
    public function view(UploadHandler $uploadHandler, Request $request): Response
    {
        $upload = new Upload();
        $uploadForm = $this->createForm(UploadType::class, $upload);
        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {
            $uploadHandler->saveImage($upload);
            $this->addFlash('notice', 'File "'.$upload.'" has been uploaded.');
        }

        return $this->render('admin/content/image/view.twig', [
            'area' => 'content',
            'subarea' => 'image',
            'images' => $uploadHandler->getImages(),
            'uploadForm' => $uploadForm->createView()
        ]);
    }

    /**
     * @Route("/delete/{filename}", name="delete")
     */
    public function delete(string $filename, UploadHandler $uploadHandler, Request $request): Response
    {
        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $uploadHandler->deleteImage($filename);
            $this->addFlash('notice', 'File "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_content_images_view');
        }

        return $this->render('admin/content/image/delete.twig', [
            'area' => 'content',
            'subarea' => 'image',
            'filename' => $filename,
            'uploadForm' => $form->createView()
        ]);
    }
}
