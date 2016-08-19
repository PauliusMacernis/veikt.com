<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160818154106 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, base_salary NUMERIC(19, 4) DEFAULT NULL, benefits LONGTEXT DEFAULT NULL, date_posted DATETIME DEFAULT NULL, education_requirements LONGTEXT DEFAULT NULL, employment_type VARCHAR(255) DEFAULT NULL, experience_requirements LONGTEXT DEFAULT NULL, hiring_organization LONGTEXT DEFAULT NULL, incentives LONGTEXT DEFAULT NULL, industry VARCHAR(255) DEFAULT NULL, job_location VARCHAR(255) DEFAULT NULL, occupational_category VARCHAR(255) DEFAULT NULL, qualifications LONGTEXT DEFAULT NULL, responsibilities LONGTEXT DEFAULT NULL, salary_currency VARCHAR(255) DEFAULT NULL, skills LONGTEXT DEFAULT NULL, special_commitments LONGTEXT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, work_hours VARCHAR(255) DEFAULT NULL, additional_type VARCHAR(255) DEFAULT NULL, alternate_name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, potential_action VARCHAR(255) DEFAULT NULL, same_as VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, step1_id_in_source_system LONGTEXT DEFAULT NULL, step1_html LONGTEXT DEFAULT NULL, step1_statistics LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE job');
    }
}
