<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260920151343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, invoice_number VARCHAR(50) NOT NULL, is_paid TINYINT NOT NULL, issued_at DATETIME NOT NULL, devis_id INT NOT NULL, UNIQUE INDEX UNIQ_FE86641041DEFADA (devis_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641041DEFADA FOREIGN KEY (devis_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641041DEFADA');
        $this->addSql('DROP TABLE facture');
    }
}
