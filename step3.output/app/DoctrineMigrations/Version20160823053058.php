<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160823053058 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job CHANGE is_published is_published TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE job_note ADD job_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE job_note ADD CONSTRAINT FK_48718C55BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id)');
        $this->addSql('CREATE INDEX IDX_48718C55BE04EA9 ON job_note (job_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job CHANGE is_published is_published TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE job_note DROP FOREIGN KEY FK_48718C55BE04EA9');
        $this->addSql('DROP INDEX IDX_48718C55BE04EA9 ON job_note');
        $this->addSql('ALTER TABLE job_note DROP job_id');
    }
}
