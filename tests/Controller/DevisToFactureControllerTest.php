<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\Devis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DevisToFactureControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;

    private function createTestClient(): Client
    {
        $client = new Client();
        $client->setName('Test Client');
        $client->setEmail('test' . uniqid() . '@example.com');
        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    private function createDevis(string $status): Devis
    {
        $devis = new Devis();
        $devis->setClient($this->createTestClient());
        $devis->setStatus($status);
        $devis->setTotalAmount(1000.0);
        $devis->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($devis);
        $this->em->flush();

        return $devis;
    }

    public function testCanConvertAcceptedDevisToFacture(): void
    {
        $httpClient = static::createClient();
        $this->em = $httpClient->getContainer()->get('doctrine')->getManager();

        $devis = $this->createDevis('accepted');

        $httpClient->request('POST', '/api/devis/' . $devis->getId() . '/convert-to-facture');

        self::assertResponseStatusCodeSame(201);
        $data = json_decode($httpClient->getResponse()->getContent(), true);
        self::assertArrayHasKey('invoiceNumber', $data);
        self::assertStringStartsWith('FAC-', $data['invoiceNumber']);
    }

    public function testCannotConvertNonAcceptedDevis(): void
    {
        $httpClient = static::createClient();
        $this->em = $httpClient->getContainer()->get('doctrine')->getManager();

        $devis = $this->createDevis('pending');

        $httpClient->request('POST', '/api/devis/' . $devis->getId() . '/convert-to-facture');

        self::assertResponseStatusCodeSame(422);
    }

    public function testCannotConvertSameDevisTwice(): void
    {
        $httpClient = static::createClient();
        $this->em = $httpClient->getContainer()->get('doctrine')->getManager();

        $devis = $this->createDevis('accepted');

        $httpClient->request('POST', '/api/devis/' . $devis->getId() . '/convert-to-facture');
        self::assertResponseStatusCodeSame(201);

        $httpClient->request('POST', '/api/devis/' . $devis->getId() . '/convert-to-facture');
        self::assertResponseStatusCodeSame(422);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->em);
    }
}