<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160818200833 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job CHANGE step1_id step1_id LONGTEXT NOT NULL, CHANGE step1_html step1_html LONGTEXT NOT NULL, CHANGE step1_statistics step1_statistics LONGTEXT NOT NULL, CHANGE step1_project step1_project VARCHAR(255) NOT NULL, CHANGE step1_url step1_url VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job CHANGE step1_id step1_id LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE step1_html step1_html LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE step1_statistics step1_statistics LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE step1_project step1_project VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE step1_url step1_url VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
