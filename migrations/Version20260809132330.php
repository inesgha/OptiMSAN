<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260809132330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_ligne (id INT AUTO_INCREMENT NOT NULL, code_demande VARCHAR(50) NOT NULL, type_offre VARCHAR(100) NOT NULL, etat_demande VARCHAR(180) NOT NULL, adresse_installation VARCHAR(150) NOT NULL, date_demande DATETIME NOT NULL, client_id INT DEFAULT NULL, portmsan_id INT DEFAULT NULL, INDEX IDX_A12E58AA19EB6921 (client_id), INDEX IDX_A12E58AAC27A587E (portmsan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE port_msan (id INT AUTO_INCREMENT NOT NULL, numero_port INT NOT NULL, type_port VARCHAR(255) NOT NULL, etat_port VARCHAR(250) NOT NULL, date_affectation DATE NOT NULL, slotcard_id INT NOT NULL, INDEX IDX_7715CCDBBFCF5A1 (slotcard_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE qualification_ligne (id INT AUTO_INCREMENT NOT NULL, standard VARCHAR(150) NOT NULL, qualite_globale VARCHAR(50) NOT NULL, channel_rate_down INT NOT NULL, channel_rate_up INT NOT NULL, snr_margin_down DOUBLE PRECISION NOT NULL, snr_margin_up DOUBLE PRECISION NOT NULL, attenuation_down DOUBLE PRECISION NOT NULL, attenuation_up DOUBLE PRECISION NOT NULL, distance_km DOUBLE PRECISION NOT NULL, date_test DATETIME NOT NULL, technicien_id INT NOT NULL, portmsan_id INT NOT NULL, INDEX IDX_841F6F0713457256 (technicien_id), INDEX IDX_841F6F07C27A587E (portmsan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, code_ticket VARCHAR(150) NOT NULL, type_panne VARCHAR(150) NOT NULL, description VARCHAR(255) DEFAULT NULL, etat_ticket VARCHAR(250) NOT NULL, priorite VARCHAR(250) NOT NULL, date_creation DATETIME NOT NULL, date_resolution DATETIME NOT NULL, client_id INT DEFAULT NULL, technicien_id INT DEFAULT NULL, portmsan_id INT DEFAULT NULL, INDEX IDX_CE60640419EB6921 (client_id), INDEX IDX_CE60640413457256 (technicien_id), INDEX IDX_CE606404C27A587E (portmsan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE shelf (id INT AUTO_INCREMENT NOT NULL, numero_shelf INT NOT NULL, adresse_ip_sub VARCHAR(150) NOT NULL, sitemsan_id INT DEFAULT NULL, INDEX IDX_A5475BE3958D0607 (sitemsan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE slot_card (id INT AUTO_INCREMENT NOT NULL, numero_slot INT NOT NULL, type_carte VARCHAR(150) NOT NULL, nombre_ports_total INT NOT NULL, shelf_id INT NOT NULL, INDEX IDX_C83D32447C12FBC0 (shelf_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE demande_ligne ADD CONSTRAINT FK_A12E58AA19EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE demande_ligne ADD CONSTRAINT FK_A12E58AAC27A587E FOREIGN KEY (portmsan_id) REFERENCES port_msan (id)');
        $this->addSql('ALTER TABLE port_msan ADD CONSTRAINT FK_7715CCDBBFCF5A1 FOREIGN KEY (slotcard_id) REFERENCES slot_card (id)');
        $this->addSql('ALTER TABLE qualification_ligne ADD CONSTRAINT FK_841F6F0713457256 FOREIGN KEY (technicien_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE qualification_ligne ADD CONSTRAINT FK_841F6F07C27A587E FOREIGN KEY (portmsan_id) REFERENCES port_msan (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640419EB6921 FOREIGN KEY (client_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE60640413457256 FOREIGN KEY (technicien_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404C27A587E FOREIGN KEY (portmsan_id) REFERENCES port_msan (id)');
        $this->addSql('ALTER TABLE shelf ADD CONSTRAINT FK_A5475BE3958D0607 FOREIGN KEY (sitemsan_id) REFERENCES site_msan (id)');
        $this->addSql('ALTER TABLE slot_card ADD CONSTRAINT FK_C83D32447C12FBC0 FOREIGN KEY (shelf_id) REFERENCES shelf (id)');
        $this->addSql('ALTER TABLE site_msan ADD zone_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE site_msan ADD CONSTRAINT FK_53C98D8B9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('CREATE INDEX IDX_53C98D8B9F2C3FAB ON site_msan (zone_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_ligne DROP FOREIGN KEY FK_A12E58AA19EB6921');
        $this->addSql('ALTER TABLE demande_ligne DROP FOREIGN KEY FK_A12E58AAC27A587E');
        $this->addSql('ALTER TABLE port_msan DROP FOREIGN KEY FK_7715CCDBBFCF5A1');
        $this->addSql('ALTER TABLE qualification_ligne DROP FOREIGN KEY FK_841F6F0713457256');
        $this->addSql('ALTER TABLE qualification_ligne DROP FOREIGN KEY FK_841F6F07C27A587E');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640419EB6921');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE60640413457256');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404C27A587E');
        $this->addSql('ALTER TABLE shelf DROP FOREIGN KEY FK_A5475BE3958D0607');
        $this->addSql('ALTER TABLE slot_card DROP FOREIGN KEY FK_C83D32447C12FBC0');
        $this->addSql('DROP TABLE demande_ligne');
        $this->addSql('DROP TABLE port_msan');
        $this->addSql('DROP TABLE qualification_ligne');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE shelf');
        $this->addSql('DROP TABLE slot_card');
        $this->addSql('ALTER TABLE site_msan DROP FOREIGN KEY FK_53C98D8B9F2C3FAB');
        $this->addSql('DROP INDEX IDX_53C98D8B9F2C3FAB ON site_msan');
        $this->addSql('ALTER TABLE site_msan DROP zone_id');
    }
}
