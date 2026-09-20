<?php

namespace App\Controller;

use App\Entity\Facture;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class FacturePdfController extends AbstractController
{
    #[Route('/api/factures/{id}/pdf', name: 'facture_pdf', methods: ['GET'])]
    public function generatePdf(Facture $facture, Environment $twig): Response
    {
        $html = $twig->render('facture/pdf.html.twig', [
            'facture' => $facture,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="facture-' . $facture->getInvoiceNumber() . '.pdf"',
            ]
        );
    }
}