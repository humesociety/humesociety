<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Text\TextHandler;
use App\Entity\Text\TextType;
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
     * Route for viewing conference text variables and email templates.
     *
     * @param TextHandler The text handler.
     * @return Response
     * @Route("/{tab}", name="index", requirements={"tab": "%conference_text_group_ids%"})
     */
    public function index(TextHandler $texts, string $tab = 'submission'): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'conference',
            'subarea' => 'text',
            'tab' => $tab,
            'textGroups' => $texts->getConferenceTextGroups()
        ];

        // render and return the page
        return $this->render('admin/conference/text/index.twig', $twigs);
    }

    /**
     * Route for editing a conference text variable.
     *
     * @param Request Symfony's request object.
     * @param TextHandler The text handler.
     * @param string The text's label.
     * @return Response
     * @Route("/edit/{label}", name="edit", requirements={"label": "%conference_text_ids%"})
     */
    public function edit(Request $request, TextHandler $texts, string $label): Response
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
        $textForm->handleRequest($request);
        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $texts->saveText($text);
            $this->addFlash('notice', $text.' text has been updated.');
            return $this->redirectToRoute('admin_conference_text_index', ['tab' => $text->getGroup()]);
        }

        // add additional twig variables
        $twigs['textForm'] = $textForm->createView();
        $twigs['formName'] = $textForm->getName(); // for the preview

        // render and return the page
        return $this->render('admin/conference/text/edit.twig', $twigs);
    }
}
