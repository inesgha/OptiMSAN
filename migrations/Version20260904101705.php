<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260904101705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_ligne DROP FOREIGN KEY `FK_A12E58AAC27A587E`');
        $this->addSql('DROP INDEX IDX_A12E58AAC27A587E ON demande_ligne');
        $this->addSql('ALTER TABLE demande_ligne CHANGE portmsan_id port_msan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_ligne ADD CONSTRAINT FK_A12E58AACE3A93D6 FOREIGN KEY (port_msan_id) REFERENCES port_msan (id)');
        $this->addSql('CREATE INDEX IDX_A12E58AACE3A93D6 ON demande_ligne (port_msan_id)');
        $this->addSql('ALTER TABLE port_msan DROP FOREIGN KEY `FK_7715CCDBBFCF5A1`');
        $this->addSql('DROP INDEX IDX_7715CCDBBFCF5A1 ON port_msan');
        $this->addSql('ALTER TABLE port_msan CHANGE slotcard_id slot_card_id INT NOT NULL');
        $this->addSql('ALTER TABLE port_msan ADD CONSTRAINT FK_7715CCDA4CAF884 FOREIGN KEY (slot_card_id) REFERENCES slot_card (id)');
        $this->addSql('CREATE INDEX IDX_7715CCDA4CAF884 ON port_msan (slot_card_id)');
        $this->addSql('ALTER TABLE qualification_ligne DROP FOREIGN KEY `FK_841F6F07C27A587E`');
        $this->addSql('DROP INDEX IDX_841F6F07C27A587E ON qualification_ligne');
        $this->addSql('ALTER TABLE qualification_ligne CHANGE portmsan_id port_msan_id INT NOT NULL');
        $this->addSql('ALTER TABLE qualification_ligne ADD CONSTRAINT FK_841F6F07CE3A93D6 FOREIGN KEY (port_msan_id) REFERENCES port_msan (id)');
        $this->addSql('CREATE INDEX IDX_841F6F07CE3A93D6 ON qualification_ligne (port_msan_id)');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY `FK_CE606404C27A587E`');
        $this->addSql('DROP INDEX IDX_CE606404C27A587E ON reclamation');
        $this->addSql('ALTER TABLE reclamation CHANGE date_resolution date_resolution DATETIME DEFAULT NULL, CHANGE portmsan_id port_msan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404CE3A93D6 FOREIGN KEY (port_msan_id) REFERENCES port_msan (id)');
        $this->addSql('CREATE INDEX IDX_CE606404CE3A93D6 ON reclamation (port_msan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_ligne DROP FOREIGN KEY FK_A12E58AACE3A93D6');
        $this->addSql('DROP INDEX IDX_A12E58AACE3A93D6 ON demande_ligne');
        $this->addSql('ALTER TABLE demande_ligne CHANGE port_msan_id portmsan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_ligne ADD CONSTRAINT `FK_A12E58AAC27A587E` FOREIGN KEY (portmsan_id) REFERENCES port_msan (id)');
        $this->addSql('CREATE INDEX IDX_A12E58AAC27A587E ON demande_ligne (portmsan_id)');
        $this->addSql('ALTER TABLE port_msan DROP FOREIGN KEY FK_7715CCDA4CAF884');
        $this->addSql('DROP INDEX IDX_7715CCDA4CAF884 ON port_msan');
        $this->addSql('ALTER TABLE port_msan CHANGE slot_card_id slotcard_id INT NOT NULL');
        $this->addSql('ALTER TABLE port_msan ADD CONSTRAINT `FK_7715CCDBBFCF5A1` FOREIGN KEY (slotcard_id) REFERENCES slot_card (id)');
        $this->addSql('CREATE INDEX IDX_7715CCDBBFCF5A1 ON port_msan (slotcard_id)');
        $this->addSql('ALTER TABLE qualification_ligne DROP FOREIGN KEY FK_841F6F07CE3A93D6');
        $this->addSql('DROP INDEX IDX_841F6F07CE3A93D6 ON qualification_ligne');
        $this->addSql('ALTER TABLE qualification_ligne CHANGE port_msan_id portmsan_id INT NOT NULL');
        $this->addSql('ALTER TABLE qualification_ligne ADD CONSTRAINT `FK_841F6F07C27A587E` FOREIGN KEY (portmsan_id) REFERENCES port_msan (id)');
        $this->addSql('CREATE INDEX IDX_841F6F07C27A587E ON qualification_ligne (portmsan_id)');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404CE3A93D6');
        $this->addSql('DROP INDEX IDX_CE606404CE3A93D6 ON reclamation');
        $this->addSql('ALTER TABLE reclamation CHANGE date_resolution date_resolution DATETIME NOT NULL, CHANGE port_msan_id portmsan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT `FK_CE606404C27A587E` FOREIGN KEY (portmsan_id) REFERENCES port_msan (id)');
        $this->addSql('CREATE INDEX IDX_CE606404C27A587E ON reclamation (portmsan_id)');
    }
}
