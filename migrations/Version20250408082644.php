<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408082644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu DROP FOREIGN KEY FK_2F577D59A73F0036
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ville
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2F577D59A73F0036 ON lieu
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD ville VARCHAR(255) NOT NULL, ADD code_postal VARCHAR(5) NOT NULL, DROP ville_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ville (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, code_postal VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD ville_id INT NOT NULL, DROP ville, DROP code_postal
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD CONSTRAINT FK_2F577D59A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2F577D59A73F0036 ON lieu (ville_id)
        SQL);
    }
}
