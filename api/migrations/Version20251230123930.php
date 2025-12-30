<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230123930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authoring_directive (id VARCHAR NOT NULL, state VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, discr VARCHAR(255) NOT NULL, rule_content TEXT DEFAULT NULL, skill_content TEXT DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE authoring_rule_example (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, good TEXT DEFAULT NULL, bad TEXT DEFAULT NULL, explanation TEXT DEFAULT NULL, rule_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7330E1B6744E0351 ON authoring_rule_example (rule_id)');
        $this->addSql('CREATE TABLE authoring_skill_example (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, scenario TEXT NOT NULL, input TEXT NOT NULL, output TEXT NOT NULL, explanation TEXT DEFAULT NULL, skill_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FA4AA20C5585C142 ON authoring_skill_example (skill_id)');
        $this->addSql('CREATE TABLE authoring_skill_step (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "order" INT NOT NULL, content TEXT NOT NULL, skill_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_CBD6A61E5585C142 ON authoring_skill_step (skill_id)');
        $this->addSql('ALTER TABLE authoring_rule_example ADD CONSTRAINT FK_7330E1B6744E0351 FOREIGN KEY (rule_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE authoring_skill_example ADD CONSTRAINT FK_FA4AA20C5585C142 FOREIGN KEY (skill_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE authoring_skill_step ADD CONSTRAINT FK_CBD6A61E5585C142 FOREIGN KEY (skill_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE authoring_rule_example DROP CONSTRAINT FK_7330E1B6744E0351');
        $this->addSql('ALTER TABLE authoring_skill_example DROP CONSTRAINT FK_FA4AA20C5585C142');
        $this->addSql('ALTER TABLE authoring_skill_step DROP CONSTRAINT FK_CBD6A61E5585C142');
        $this->addSql('DROP TABLE authoring_directive');
        $this->addSql('DROP TABLE authoring_rule_example');
        $this->addSql('DROP TABLE authoring_skill_example');
        $this->addSql('DROP TABLE authoring_skill_step');
    }
}
