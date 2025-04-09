<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionXXXXXXXXXXXX extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute une image par défaut à tous les participants';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE participant SET url_photo = 'default-avatar.png' WHERE url_photo IS NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE participant SET url_photo = NULL WHERE url_photo = 'default-avatar.png'");
    }
}