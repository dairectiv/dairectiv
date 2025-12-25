<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225193639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authoring_directive (id VARCHAR NOT NULL, state VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR NOT NULL, description TEXT NOT NULL, discr VARCHAR(255) NOT NULL, content TEXT DEFAULT NULL, examples JSON DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE authoring_directive_version (id VARCHAR NOT NULL, number INT NOT NULL, snapshot JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, directive_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_42CBB5B21582AB65 ON authoring_directive_version (directive_id)');
        $this->addSql('ALTER TABLE authoring_directive_version ADD CONSTRAINT FK_42CBB5B21582AB65 FOREIGN KEY (directive_id) REFERENCES authoring_directive (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authoring_directive_version DROP CONSTRAINT FK_42CBB5B21582AB65');
        $this->addSql('DROP TABLE authoring_directive');
        $this->addSql('DROP TABLE authoring_directive_version');
    }
}
