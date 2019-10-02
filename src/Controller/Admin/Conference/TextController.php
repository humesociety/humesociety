<?php

namespace App\Controller\Admin\Conference;

use App\Entity\Conference\ConferenceHandler;
use App\Entity\Text\Text;
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
     * Get a text's title.
     *
     * @param Text The text.
     * @return string
     */
    private function textTitle(Text $text): string
    {
        return $this->getParameter('conference_texts')[$text->getLabel()]['title'];
    }

    /**
     * The conference texts index page.
     *
     * @return Response
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_text_view');
    }

    /**
     * The page for viewing all texts.
     *
     * @return Response
     * @Route("/view", name="view")
     */
    public function view(): Response
    {
        return $this->render('admin/conference/text/view.twig', [
            'area' => 'conference',
            'subarea' => 'text'
        ]);
    }

    /**
     * The page for editing some text.
     *
     * @param Request Symfony's request object.
     * @param ConferenceHandler The conference handler.
     * @param TextHandler The text handler.
     * @param string The text's label.
     * @return Response
     * @Route("/edit/{label}", name="edit", requirements={"label": "%conference_text_ids%"})
     */
    public function edit(
        Request $request,
        ConferenceHandler $conferenceHandler,
        TextHandler $textHandler,
        string $label
    ): Response {
        // look for the text
        $text = $textHandler->getTextByLabel($label);

        // create a new one if it doesn't exist
        if (!$text) {
            $text = new Text();
            $text->setLabel($label);
        }

        // text form
        $form = $this->createForm(TextType::class, $text);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $textHandler->saveText($text);
            $this->addFlash('notice', $this->textTitle($text).' text has been updated.');
            return $this->redirectToRoute('admin_conference_text_view');
        }

        // return the response
        return $this->render('admin/conference/text/edit.twig', [
            'area' => 'conference',
            'subarea' => 'text',
            'text' => $text,
            'textForm' => $form->createView()
        ]);
    }
}
