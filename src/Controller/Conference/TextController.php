<?php

namespace App\Controller\Conference;

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
 * @Route("/conference-manager/text", name="conference_text_")
 * @IsGranted("ROLE_ORGANISER")
 */
class TextController extends AbstractController
{
    /**
     * Route for viewing conference text variables and email templates.
     *
     * @param TextHandler $texts The text handler.
     * @return Response
     * @Route("/", name="index")
     */
    public function index(TextHandler $texts): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'manager',
            'subarea' => 'text',
            'texts' => $texts->getConferenceTexts()
        ];

        // render and return the page
        return $this->render('conference/text/index.twig', $twigs);
    }

    /**
     * Route for editing a conference text variable.
     *
     * @param Request $request Symfony's request object.
     * @param TextHandler $texts The text handler.
     * @param string $label The text's label.
     * @return Response
     * @Route("/edit/{label}", name="edit", requirements={"label": "%conference_text_ids%"})
     */
    public function edit(Request $request, TextHandler $texts, string $label): Response
    {
        // get the text variable
        $text = $texts->getTextByLabel($label);

        // initialise the twig variables
        $twigs = [
            'area' => 'manager',
            'subarea' => 'text',
            'text' => $text,
        ];

        // create and handle the text edit form
        $textForm = $this->createForm(TextType::class, $text);
        $textForm->handleRequest($request);
        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $texts->saveText($text);
            $this->addFlash('notice', $text.' text has been updated.');
            return $this->redirectToRoute('conference_text_index');
        }

        // add additional twig variables
        $twigs['textForm'] = $textForm->createView();
        $twigs['formName'] = $textForm->getName(); // for the preview

        // render and return the page
        return $this->render('conference/text/edit.twig', $twigs);
    }
}
