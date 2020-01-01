<?php

namespace App\Controller\Admin;

use App\Entity\Upload\Upload;
use App\Entity\Upload\UploadTypeReport;
use App\Entity\Upload\UploadHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for managing society minutes and reports.
 *
 * @Route("/admin/report", name="admin_report_")
 * @IsGranted("ROLE_EVPT")
 */
class ReportController extends AbstractController
{
    /**
     * Route for viewing reports.
     *
     * @param UploadHandler $uploads The upload handler.
     * @param string|null $year The initial year to show.
     * @return Response
     * @Route("/{year}", name="index", requirements={"year": "\d{4}"})
     */
    public function index(UploadHandler $uploads, ?string $year = null): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'report',
            'year' => $year,
            'years' => $uploads->getReportYears(),
            'reports' => $uploads->getReports()
        ];

        // render and return the page
        return $this->render('admin/report/view.twig', $twigs);
    }

    /**
     * Upload a report.
     *
     * @param Request $request Symfony's request object.
     * @param UploadHandler $uploads The upload handler.
     * @param string $year The year of the report to set.
     * @return Response
     * @Route("/upload/{year}", name="upload", requirements={"year": "\d{4}"})
     */
    public function upload(Request $request, UploadHandler $uploads, ?string $year = null): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'report',
            'year' => $year
        ];

        // create and handle the report form
        $report = new Upload();
        $reportForm = $this->createForm(UploadTypeReport::class, $report);
        $reportForm->get('year')->setData((int) $year);
        $reportForm->handleRequest($request);
        if ($reportForm->isSubmitted() && $reportForm->isValid()) {
            $year = $reportForm['year']->getData();
            $uploads->saveReport($report, $year);
            $this->addFlash('notice', 'Report "'.$report.'" has been uploaded.');
            return $this->redirectToRoute('admin_report_index', ['year' => $year]);
        }

        // add additional twig variables
        $twigs['reportForm'] = $reportForm->createView();

        // render and return the page
        return $this->render('admin/report/upload.twig', $twigs);
    }

    /**
     * Route for deleting a report.
     *
     * @param Request $request Symfony's request object.
     * @param UploadHandler $uploads The upload handler.
     * @param string $year The year of the report.
     * @param string $filename The filename of the report.
     * @return Response
     * @Route("/delete/{year}/{filename}", name="delete", requirements={"year": "\d{4}"})
     */
    public function delete(Request $request, UploadHandler $uploads, string $year, string $filename): Response
    {
        // initialise the twig variables
        $twigs = [
            'area' => 'content',
            'subarea' => 'report',
            'year' => $year,
            'filename' => $filename
        ];

        // create and handle the report form
        $reportForm = $this->createFormBuilder()->getForm();
        $reportForm->handleRequest($request);
        if ($reportForm->isSubmitted()) {
            $uploads->deleteReport($filename, $year);
            $this->addFlash('notice', 'Report "'.$filename.'" has been deleted.');
            return $this->redirectToRoute('admin_report_index');
        }

        // add additional twig variables
        $twigs['reportForm'] = $reportForm->createView();

        // render and return the page
        return $this->render('admin/report/delete.twig', $twigs);
    }
}
