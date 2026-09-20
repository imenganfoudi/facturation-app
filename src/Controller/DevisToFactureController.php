<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DevisToFactureController extends AbstractController
{
    #[Route('/api/devis/{id}/convert-to-facture', name: 'devis_to_facture', methods: ['POST'])]
    public function convert(Devis $devis, EntityManagerInterface $em): JsonResponse
    {
        if ($devis->getStatus() !== 'accepted') {
            return new JsonResponse([
                'error' => 'Seul un devis avec le statut "accepted" peut être converti en facture.',
                'currentStatus' => $devis->getStatus(),
            ], 422);
        }

        $existingFacture = $em->getRepository(Facture::class)->findOneBy(['devis' => $devis]);
        if ($existingFacture) {
            return new JsonResponse([
                'error' => 'Ce devis a déjà été converti en facture.',
                'factureId' => $existingFacture->getId(),
            ], 422);
        }

        $facture = new Facture();
        $facture->setDevis($devis);
        $facture->setInvoiceNumber('FAC-' . date('Y') . '-' . str_pad((string) $devis->getId(), 5, '0', STR_PAD_LEFT));
        $facture->setIsPaid(false);
        $facture->setIssuedAt(new \DateTimeImmutable());

        $em->persist($facture);
        $em->flush();

        return new JsonResponse([
            'message' => 'Facture créée avec succès.',
            'factureId' => $facture->getId(),
            'invoiceNumber' => $facture->getInvoiceNumber(),
        ], 201);
    }
}