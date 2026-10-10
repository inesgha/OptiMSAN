<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les notes d'intervention du technicien : elles ne remplacent plus
 * la description saisie par le client.
 */
final class Version20261009120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réclamation : colonne notes_intervention (notes du technicien séparées de la description du client)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reclamation ADD notes_intervention LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reclamation DROP notes_intervention');
    }
}
