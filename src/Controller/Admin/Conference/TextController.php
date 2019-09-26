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
 * @Route("/admin/conference/text", name="admin_conference_text_")
 * @IsGranted("ROLE_ORGANISER")
 *
 * Controller for managing conference text variables.
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
     * @Route("/", name="index")
     */
    public function index() : Response
    {
        return $this->redirectToRoute('admin_conference_text_view');
    }

    /**
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
     * @Route("/edit/{label}", name="edit", requirements={"label": "%conference_text_ids%"})
     */
    public function edit(
        string $label,
        Request $request,
        ConferenceHandler $conferenceHandler,
        TextHandler $textHandler
    ): Response {
        $text = $textHandler->getTextByLabel($label);

        if (!$text) {
            $text = new Text();
            $text->setLabel($label);
        }

        $form = $this->createForm(TextType::class, $text);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $textHandler->saveText($text);
            $this->addFlash('notice', $this->textTitle($text).' text has been updated.');
            return $this->redirectToRoute('admin_conference_text_view');
        }

        return $this->render('admin/conference/text/edit.twig', [
            'area' => 'conference',
            'subarea' => 'text',
            'text' => $text,
            'textForm' => $form->createView()
        ]);
    }
}
