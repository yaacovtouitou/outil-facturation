<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228035940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE telephone telephone VARCHAR(50) NOT NULL, CHANGE adresse adresse VARCHAR(255) NOT NULL, CHANGE prenom prenom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE facture CHANGE statut statut VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY `FK_51C88FAD7F2DEE08`');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT FK_51C88FAD7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client CHANGE prenom prenom VARCHAR(255) NOT NULL, CHANGE adresse adresse VARCHAR(255) DEFAULT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE facture CHANGE statut statut VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE prestation DROP FOREIGN KEY FK_51C88FAD7F2DEE08');
        $this->addSql('ALTER TABLE prestation ADD CONSTRAINT `FK_51C88FAD7F2DEE08` FOREIGN KEY (facture_id) REFERENCES facture (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
