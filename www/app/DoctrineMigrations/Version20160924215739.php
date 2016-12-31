<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160924215739 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sub_family (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

        # Make default value
        $this->addSql('INSERT INTO sub_family (id, name) VALUES (1, \'Default SubFamily\')');

        $this->addSql('ALTER TABLE job ADD sub_family_id INT NOT NULL, CHANGE is_published is_published TINYINT(1) DEFAULT \'1\' NOT NULL');

        # Apply default value
        $this->addSql('UPDATE job SET sub_family_id = 1');

        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8D15310D4 FOREIGN KEY (sub_family_id) REFERENCES sub_family (id)');

        $this->addSql('CREATE INDEX IDX_FBD8E0F8D15310D4 ON job (sub_family_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8D15310D4');
        $this->addSql('DROP TABLE sub_family');
        $this->addSql('DROP INDEX IDX_FBD8E0F8D15310D4 ON job');
        $this->addSql('ALTER TABLE job DROP sub_family_id, CHANGE is_published is_published TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
