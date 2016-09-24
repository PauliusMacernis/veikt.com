<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160924011110 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE job (id INT UNSIGNED AUTO_INCREMENT NOT NULL, base_salary NUMERIC(19, 4) DEFAULT NULL, benefits LONGTEXT DEFAULT NULL, date_posted DATETIME DEFAULT NULL, education_requirements LONGTEXT DEFAULT NULL, employment_type VARCHAR(255) DEFAULT NULL, experience_requirements LONGTEXT DEFAULT NULL, hiring_organization LONGTEXT DEFAULT NULL, incentives LONGTEXT DEFAULT NULL, industry VARCHAR(255) DEFAULT NULL, job_location VARCHAR(255) DEFAULT NULL, occupational_category VARCHAR(255) DEFAULT NULL, qualifications LONGTEXT DEFAULT NULL, responsibilities LONGTEXT DEFAULT NULL, salary_currency VARCHAR(255) DEFAULT NULL, skills LONGTEXT DEFAULT NULL, special_commitments LONGTEXT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, work_hours VARCHAR(255) DEFAULT NULL, additional_type VARCHAR(255) DEFAULT NULL, alternate_name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, potential_action VARCHAR(255) DEFAULT NULL, same_as VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, step1_id LONGTEXT NOT NULL, step1_html LONGTEXT NOT NULL, step1_statistics LONGTEXT NOT NULL, step1_project VARCHAR(255) NOT NULL, step1_url VARCHAR(255) NOT NULL, step1_downloaded_time DATETIME NOT NULL, is_published TINYINT(1) DEFAULT \'1\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_note (id INT AUTO_INCREMENT NOT NULL, job_id INT UNSIGNED NOT NULL, username VARCHAR(255) NOT NULL, user_avatar_filename VARCHAR(255) NOT NULL, note LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_48718C55BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job_note ADD CONSTRAINT FK_48718C55BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) ON DELETE RESTRICT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_note DROP FOREIGN KEY FK_48718C55BE04EA9');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE job_note');
    }
}
