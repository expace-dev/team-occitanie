<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240225134259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD role VARCHAR(255) DEFAULT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD avatar VARCHAR(255) DEFAULT NULL, ADD description LONGTEXT NOT NULL, ADD poste VARCHAR(255) NOT NULL, DROP roles');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users ADD roles JSON NOT NULL, DROP role, DROP prenom, DROP avatar, DROP description, DROP poste');
    }
}
