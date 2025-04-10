<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410075954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES participant (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ville
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu CHANGE latitude latitude DOUBLE PRECISION NOT NULL, CHANGE longitude longitude DOUBLE PRECISION NOT NULL, CHANGE ville ville VARCHAR(255) NOT NULL, CHANGE code_postal code_postal VARCHAR(5) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant DROP last_login
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F29D1C3019
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3C3FD3F29D1C3019 ON sortie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD motif_annulation VARCHAR(255) DEFAULT NULL, DROP participant_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDADCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDAD9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ville (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, code_postal VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD participant_id INT DEFAULT NULL, DROP motif_annulation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F29D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C3FD3F29D1C3019 ON sortie (participant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD last_login DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lieu CHANGE latitude latitude DOUBLE PRECISION DEFAULT NULL, CHANGE longitude longitude DOUBLE PRECISION DEFAULT NULL, CHANGE ville ville VARCHAR(50) NOT NULL, CHANGE code_postal code_postal VARCHAR(10) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant DROP FOREIGN KEY FK_E6D4CDADCC72D953
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant DROP FOREIGN KEY FK_E6D4CDAD9D1C3019
        SQL);
    }
}
