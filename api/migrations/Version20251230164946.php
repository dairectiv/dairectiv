<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230164946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authoring_workflow_example (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, scenario TEXT NOT NULL, input TEXT NOT NULL, output TEXT NOT NULL, explanation TEXT DEFAULT NULL, workflow_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B74D5C072C7C2CBA ON authoring_workflow_example (workflow_id)');
        $this->addSql('CREATE TABLE authoring_workflow_step (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, step_order INT NOT NULL, content TEXT NOT NULL, workflow_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_887549BD2C7C2CBA ON authoring_workflow_step (workflow_id)');
        $this->addSql('ALTER TABLE authoring_workflow_example ADD CONSTRAINT FK_B74D5C072C7C2CBA FOREIGN KEY (workflow_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE authoring_workflow_step ADD CONSTRAINT FK_887549BD2C7C2CBA FOREIGN KEY (workflow_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE authoring_skill_example DROP CONSTRAINT fk_fa4aa20c5585c142');
        $this->addSql('ALTER TABLE authoring_skill_step DROP CONSTRAINT fk_cbd6a61e5585c142');
        $this->addSql('DROP TABLE authoring_skill_example');
        $this->addSql('DROP TABLE authoring_skill_step');
        $this->addSql('ALTER TABLE authoring_directive RENAME COLUMN skill_content TO workflow_content');
        $this->addSql("UPDATE authoring_directive SET discr = 'workflow' WHERE discr = 'skill'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authoring_skill_example (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, scenario TEXT NOT NULL, input TEXT NOT NULL, output TEXT NOT NULL, explanation TEXT DEFAULT NULL, skill_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_fa4aa20c5585c142 ON authoring_skill_example (skill_id)');
        $this->addSql('CREATE TABLE authoring_skill_step (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, step_order INT NOT NULL, content TEXT NOT NULL, skill_id VARCHAR NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_cbd6a61e5585c142 ON authoring_skill_step (skill_id)');
        $this->addSql('ALTER TABLE authoring_skill_example ADD CONSTRAINT fk_fa4aa20c5585c142 FOREIGN KEY (skill_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE authoring_skill_step ADD CONSTRAINT fk_cbd6a61e5585c142 FOREIGN KEY (skill_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE authoring_workflow_example DROP CONSTRAINT FK_B74D5C072C7C2CBA');
        $this->addSql('ALTER TABLE authoring_workflow_step DROP CONSTRAINT FK_887549BD2C7C2CBA');
        $this->addSql('DROP TABLE authoring_workflow_example');
        $this->addSql('DROP TABLE authoring_workflow_step');
        $this->addSql('ALTER TABLE authoring_directive RENAME COLUMN workflow_content TO skill_content');
        $this->addSql("UPDATE authoring_directive SET discr = 'skill' WHERE discr = 'workflow'");
    }
}
