<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409114425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE participant DROP last_login
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D936B2FA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie CHANGE organisateur_id organisateur_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES participant (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE participant ADD last_login DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D936B2FA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie CHANGE organisateur_id organisateur_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES participant (id) ON UPDATE NO ACTION ON DELETE SET NULL
        SQL);
    }
}
