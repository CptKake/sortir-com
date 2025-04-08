<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407142122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE sortie_participant (sortie_id INT NOT NULL, participant_id INT NOT NULL, INDEX IDX_E6D4CDADCC72D953 (sortie_id), INDEX IDX_E6D4CDAD9D1C3019 (participant_id), PRIMARY KEY(sortie_id, participant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDADCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant ADD CONSTRAINT FK_E6D4CDAD9D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F29D1C3019
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_3C3FD3F29D1C3019 ON sortie
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP participant_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant DROP FOREIGN KEY FK_E6D4CDADCC72D953
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie_participant DROP FOREIGN KEY FK_E6D4CDAD9D1C3019
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sortie_participant
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD participant_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F29D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_3C3FD3F29D1C3019 ON sortie (participant_id)
        SQL);
    }
}
