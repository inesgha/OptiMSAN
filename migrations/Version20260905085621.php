<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260905085621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE qualification_ligne ADD demande_ligne_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE qualification_ligne ADD CONSTRAINT FK_841F6F071C1AC7B4 FOREIGN KEY (demande_ligne_id) REFERENCES demande_ligne (id)');
        $this->addSql('CREATE INDEX IDX_841F6F071C1AC7B4 ON qualification_ligne (demande_ligne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE qualification_ligne DROP FOREIGN KEY FK_841F6F071C1AC7B4');
        $this->addSql('DROP INDEX IDX_841F6F071C1AC7B4 ON qualification_ligne');
        $this->addSql('ALTER TABLE qualification_ligne DROP demande_ligne_id');
    }
}
