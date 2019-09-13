<?php

namespace App\Controller\Admin\Society;

use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadReportType;
use App\Entity\Upload\UploadHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/society/report", name="admin_society_report_")
 * @IsGranted("ROLE_EVPT")
 *
 * This is the controller for managing society minutes and reports.
 */
class ReportController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->redirectToRoute('admin_society_report_view');
    }

    /**
     * @Route("/view/{year}", name="view", requirements={"year": "\d{4}"})
     */
    public function view(UploadHandler $uploadHandler, string $year = null): Response
    {
        return $this->render('admin/society/report/view.twig', [
            'area' => 'society',
            'subarea' => 'report',
            'year' => $year,
            'years' => $uploadHandler->getReportYears(),
            'reports' => $uploadHandler->getReports()
        ]);
    }

    /**
     * @Route("/delete/{year}/{filename}", name="delete", requirements={"year": "\d{4}"})
     */
    public function delete(
        string $year,
        string $filename,
        UploadHandler $uploadHandler,
        Request $request
    ): Response {
        $reportForm = $this->createFormBuilder()->getForm();
        $reportForm->handleRequest($request);

        if ($reportForm->isSubmitted()) {
            $uploadHandler->deleteReport($year, $filename);
            $this->addFlash('notice', 'Report "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_society_report_view');
        }

        return $this->render('admin/society/report/delete.twig', [
            'area' => 'society',
            'subarea' => 'report',
            'year' => $year,
            'filename' => $filename,
            'reportForm' => $reportForm->createView()
        ]);
    }

    /**
     * @Route("/upload/{year}", name="upload", requirements={"year": "\d{4}"})
     */
    public function upload(UploadHandler $uploadHandler, Request $request, int $year = null): Response
    {
        $report = new Upload();
        $reportForm = $this->createForm(UploadReportType::class, $report);
        $reportForm->get('year')->setData($year);
        $reportForm->handleRequest($request);

        if ($reportForm->isSubmitted() && $reportForm->isValid()) {
            $year = $reportForm->getData()->getYear();
            $uploadHandler->saveReport($report, $year);
            $this->addFlash('notice', 'Report "'.$report.'" has been uploaded.');
            return $this->redirectToRoute('admin_society_report_view', ['year' => $year]);
        }

        return $this->render('admin/society/report/upload.twig', [
            'area' => 'society',
            'subarea' => 'report',
            'year' => $year,
            'reportForm' => $reportForm->createView()
        ]);
    }
}
