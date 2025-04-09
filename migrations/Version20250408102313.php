<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250408102313 extends AbstractMigration
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
            DROP INDEX IDX_2F577D59A73F0036 ON lieu
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD ville VARCHAR(50) NOT NULL, ADD code_postal VARCHAR(10) NOT NULL, DROP ville_id, CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD last_login DATETIME DEFAULT NULL, DROP url_photo
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD ville_id INT NOT NULL, DROP ville, DROP code_postal, CHANGE latitude latitude DOUBLE PRECISION NOT NULL, CHANGE longitude longitude DOUBLE PRECISION NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu ADD CONSTRAINT FK_2F577D59A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2F577D59A73F0036 ON lieu (ville_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD url_photo VARCHAR(255) DEFAULT NULL, DROP last_login
        SQL);
    }
}
