<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260905073748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shelf DROP FOREIGN KEY `FK_A5475BE3958D0607`');
        $this->addSql('DROP INDEX IDX_A5475BE3958D0607 ON shelf');
        $this->addSql('ALTER TABLE shelf CHANGE sitemsan_id site_msan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shelf ADD CONSTRAINT FK_A5475BE3E7B4AD10 FOREIGN KEY (site_msan_id) REFERENCES site_msan (id)');
        $this->addSql('CREATE INDEX IDX_A5475BE3E7B4AD10 ON shelf (site_msan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shelf DROP FOREIGN KEY FK_A5475BE3E7B4AD10');
        $this->addSql('DROP INDEX IDX_A5475BE3E7B4AD10 ON shelf');
        $this->addSql('ALTER TABLE shelf CHANGE site_msan_id sitemsan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shelf ADD CONSTRAINT `FK_A5475BE3958D0607` FOREIGN KEY (sitemsan_id) REFERENCES site_msan (id)');
        $this->addSql('CREATE INDEX IDX_A5475BE3958D0607 ON shelf (sitemsan_id)');
    }
}
