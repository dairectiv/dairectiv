<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251226122340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authoring_directive ADD skill_content TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE authoring_directive ADD skill_workflow JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE authoring_directive ADD skill_examples JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE authoring_directive RENAME COLUMN content TO rule_content');
        $this->addSql('ALTER TABLE authoring_directive RENAME COLUMN examples TO rule_examples');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authoring_directive ADD content TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE authoring_directive ADD examples JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE authoring_directive DROP rule_content');
        $this->addSql('ALTER TABLE authoring_directive DROP rule_examples');
        $this->addSql('ALTER TABLE authoring_directive DROP skill_content');
        $this->addSql('ALTER TABLE authoring_directive DROP skill_workflow');
        $this->addSql('ALTER TABLE authoring_directive DROP skill_examples');
    }
}
