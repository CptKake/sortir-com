<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250409114750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rend la relation organisateur dans Sortie nullable et ajoute le onDelete SET NULL';
    }

    public function up(Schema $schema): void
    {
        // Rendre la relation nullable et ajouter la contrainte ON DELETE SET NULL
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D936B2FA');
        $this->addSql('ALTER TABLE sortie CHANGE organisateur_id organisateur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES participant (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // Restaurer la contrainte initiale sans nullable
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2D936B2FA');
        $this->addSql('ALTER TABLE sortie CHANGE organisateur_id organisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2D936B2FA FOREIGN KEY (organisateur_id) REFERENCES participant (id)');
    }
}
