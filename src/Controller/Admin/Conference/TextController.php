<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Text\Text;
use App\Entity\Text\TextType;
use App\Service\TextManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing conference text variables.
 *
 * @Route("/admin/conference/text", name="admin_conference_text_")
 * @IsGranted("ROLE_ORGANISER")
 */
class TextController extends AbstractController
{
    /**
     * Route for viewing conference text variables.
     *
     * @param TextManager The text manager.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(TextManager $texts): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'text',
            'conferenceTexts' => $texts->getConferenceTexts()
        ];

        // render and return the page
        return $this->render('admin/conference/text/view.twig', $twigs);
    }

    /**
     * Route for editing a conference text variable.
     *
     * @param Request Symfony's request object.
     * @param TextManager The text manager.
     * @param string The text's label.
     * @return Response
     * @Route("/edit/{label}", name="edit", requirements={"label": "%conference_text_ids%"})
     */
    public function edit(Request $request, TextManager $texts, string $label): Response
    {
        // get the text variable
        $text = $texts->getTextByLabel($label);

        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'text',
            'text' => $text,
        ];

        // create and handle the text edit form
        $textForm = $this->createForm(TextType::class, $text);
        $twigs['textForm'] = $textForm->createView();
        $twigs['formName'] = $textForm->getName(); // for the preview
        $textForm->handleRequest($request);
        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $texts->saveText($text);
            $this->addFlash('notice', $text.' text has been updated.');
            return $this->redirectToRoute('admin_conference_text_index');
        }

        // render and return the page
        return $this->render('admin/conference/text/edit.twig', $twigs);
    }
}
