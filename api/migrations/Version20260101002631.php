<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add ON UPDATE CASCADE to foreign key constraints referencing authoring_directive(id).
 * This allows changing directive IDs when archiving or deleting.
 */
final class Version20260101002631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ON UPDATE CASCADE to foreign key constraints referencing authoring_directive(id)';
    }

    public function up(Schema $schema): void
    {
        // authoring_rule_example
        $this->addSql('ALTER TABLE authoring_rule_example DROP CONSTRAINT fk_7330e1b6744e0351');
        $this->addSql('ALTER TABLE authoring_rule_example ADD CONSTRAINT fk_7330e1b6744e0351 FOREIGN KEY (rule_id) REFERENCES authoring_directive (id) ON UPDATE CASCADE ON DELETE CASCADE NOT DEFERRABLE');

        // authoring_workflow_example
        $this->addSql('ALTER TABLE authoring_workflow_example DROP CONSTRAINT fk_b74d5c072c7c2cba');
        $this->addSql('ALTER TABLE authoring_workflow_example ADD CONSTRAINT fk_b74d5c072c7c2cba FOREIGN KEY (workflow_id) REFERENCES authoring_directive (id) ON UPDATE CASCADE ON DELETE CASCADE NOT DEFERRABLE');

        // authoring_workflow_step
        $this->addSql('ALTER TABLE authoring_workflow_step DROP CONSTRAINT fk_887549bd2c7c2cba');
        $this->addSql('ALTER TABLE authoring_workflow_step ADD CONSTRAINT fk_887549bd2c7c2cba FOREIGN KEY (workflow_id) REFERENCES authoring_directive (id) ON UPDATE CASCADE ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // authoring_rule_example
        $this->addSql('ALTER TABLE authoring_rule_example DROP CONSTRAINT fk_7330e1b6744e0351');
        $this->addSql('ALTER TABLE authoring_rule_example ADD CONSTRAINT fk_7330e1b6744e0351 FOREIGN KEY (rule_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');

        // authoring_workflow_example
        $this->addSql('ALTER TABLE authoring_workflow_example DROP CONSTRAINT fk_b74d5c072c7c2cba');
        $this->addSql('ALTER TABLE authoring_workflow_example ADD CONSTRAINT fk_b74d5c072c7c2cba FOREIGN KEY (workflow_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');

        // authoring_workflow_step
        $this->addSql('ALTER TABLE authoring_workflow_step DROP CONSTRAINT fk_887549bd2c7c2cba');
        $this->addSql('ALTER TABLE authoring_workflow_step ADD CONSTRAINT fk_887549bd2c7c2cba FOREIGN KEY (workflow_id) REFERENCES authoring_directive (id) ON DELETE CASCADE NOT DEFERRABLE');
    }
}
