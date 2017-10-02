<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171002222550 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user (user_id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(30) DEFAULT NULL, last_name VARCHAR(30) DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE finance_account (finance_account_id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(30) NOT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_90A4BA00A76ED395 (user_id), PRIMARY KEY(finance_account_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE finance_movement (id INT AUTO_INCREMENT NOT NULL, finance_account_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, description VARCHAR(255) NOT NULL, date DATE NOT NULL, fixed TINYINT(1) NOT NULL, INDEX IDX_258B60EAA9D0E463 (finance_account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE finance_account ADD CONSTRAINT FK_90A4BA00A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('ALTER TABLE finance_movement ADD CONSTRAINT FK_258B60EAA9D0E463 FOREIGN KEY (finance_account_id) REFERENCES finance_account (finance_account_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE finance_account DROP FOREIGN KEY FK_90A4BA00A76ED395');
        $this->addSql('ALTER TABLE finance_movement DROP FOREIGN KEY FK_258B60EAA9D0E463');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE finance_account');
        $this->addSql('DROP TABLE finance_movement');
    }
}
