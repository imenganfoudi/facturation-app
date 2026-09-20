<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\Facture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/api/dashboard/stats', name: 'dashboard_stats', methods: ['GET'])]
    public function stats(EntityManagerInterface $em): JsonResponse
    {
        $totalClients = $em->getRepository(Client::class)->count([]);
        $totalDevis = $em->getRepository(Devis::class)->count([]);
        $acceptedDevis = $em->getRepository(Devis::class)->count(['status' => 'accepted']);
        $pendingDevis = $em->getRepository(Devis::class)->count(['status' => 'pending']);

        $totalFactures = $em->getRepository(Facture::class)->count([]);
        $paidFactures = $em->getRepository(Facture::class)->count(['isPaid' => true]);
        $unpaidFactures = $em->getRepository(Facture::class)->count(['isPaid' => false]);

        $qb = $em->createQueryBuilder();
        $totalRevenue = $qb->select('SUM(d.totalAmount)')
            ->from(Devis::class, 'd')
            ->join(Facture::class, 'f', 'WITH', 'f.devis = d')
            ->where('f.isPaid = true')
            ->getQuery()
            ->getSingleScalarResult();

        return new JsonResponse([
            'clients' => [
                'total' => $totalClients,
            ],
            'devis' => [
                'total' => $totalDevis,
                'accepted' => $acceptedDevis,
                'pending' => $pendingDevis,
            ],
            'factures' => [
                'total' => $totalFactures,
                'paid' => $paidFactures,
                'unpaid' => $unpaidFactures,
            ],
            'revenue' => [
                'totalPaid' => $totalRevenue ?? 0,
            ],
        ]);
    }
}