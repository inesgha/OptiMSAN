<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260809115737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE site_msan (id INT AUTO_INCREMENT NOT NULL, nom_site VARCHAR(200) NOT NULL, adresse_ip VARCHAR(100) NOT NULL, type_msan VARCHAR(100) NOT NULL, constructeur VARCHAR(150) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, role_type VARCHAR(255) NOT NULL, matricule VARCHAR(150) DEFAULT NULL, latitude_actuelle DOUBLE PRECISION DEFAULT NULL, longitude_actuelle DOUBLE PRECISION DEFAULT NULL, disponible TINYINT DEFAULT NULL, niveau_acces INT DEFAULT NULL, cin VARCHAR(20) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, nom_zone VARCHAR(100) NOT NULL, code_postal VARCHAR(30) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE site_msan');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE zone');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
