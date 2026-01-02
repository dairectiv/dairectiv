<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DataFixtures;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Step\Step;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class WorkflowFixtures extends Fixture
{
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        try {
            $this->createDraftWorkflows();
            $this->createPublishedWorkflows();
            $this->createArchivedWorkflows();
            $this->createDeletedWorkflows();

            DomainEventQueue::reset();
            $manager->flush();
        } finally {
            Chronos::setTestNow(null);
        }
    }

    private function createDraftWorkflows(): void
    {
        // Draft - empty (created 2 days ago)
        $this->at('-2 days');
        $workflow = $this->draft('workflow-draft-empty', 'Empty Workflow', 'A new workflow waiting for steps.');
        $this->save($workflow);

        // Draft - with content only (created 5 days ago, updated 1 day ago)
        $this->at('-5 days');
        $workflow = $this->draft('workflow-draft-content-only', 'Code Review Process', 'Standard code review workflow.');
        $this->at('-1 day');
        $workflow->updateContent('This workflow guides the code review process from submission to approval.');
        $this->save($workflow);

        // Draft - with steps only (created 1 week ago)
        $this->at('-1 week');
        $workflow = $this->draft('workflow-draft-steps-only', 'Bug Fix Workflow', 'Steps for fixing bugs efficiently.');
        Step::create($workflow, 'Reproduce the bug in a local environment');
        Step::create($workflow, 'Write a failing test that exposes the bug');
        Step::create($workflow, 'Fix the code to make the test pass');
        Step::create($workflow, 'Run the full test suite');
        $this->save($workflow);

        // Draft - with content and steps (created 10 days ago)
        $this->at('-10 days');
        $workflow = $this->draft('workflow-draft-content-steps', 'Feature Development', 'End-to-end feature implementation.');
        $workflow->updateContent('Follow this workflow when implementing new features.');
        Step::create($workflow, 'Review the feature specification');
        Step::create($workflow, 'Create a feature branch');
        Step::create($workflow, 'Implement the feature with tests');
        Step::create($workflow, 'Submit pull request for review');
        Step::create($workflow, 'Address review feedback');
        Step::create($workflow, 'Merge after approval');
        $this->save($workflow);

        // Draft - with examples (created 2 weeks ago)
        $this->at('-2 weeks');
        $workflow = $this->draft('workflow-draft-examples', 'Git Commit Workflow', 'Creating good commits.');
        $workflow->updateContent('Follow these steps for clean commit history.');
        Example::create(
            $workflow,
            scenario: 'Adding a new feature',
            input: 'git commit -m "stuff"',
            output: 'git commit -m "feat(auth): add password reset functionality"',
            explanation: 'Use conventional commits for clear history.',
        );
        $this->save($workflow);

        // Draft - complete with steps and examples (created 3 weeks ago)
        $this->at('-3 weeks');
        $workflow = $this->draft('workflow-draft-complete', 'PR Review Workflow', 'Complete PR review process.');
        $workflow->updateContent('This workflow ensures thorough code review for all pull requests.');
        Step::create($workflow, 'Check if CI passes');
        Step::create($workflow, 'Review code changes line by line');
        Step::create($workflow, 'Test functionality locally');
        Step::create($workflow, 'Leave constructive feedback');
        Step::create($workflow, 'Approve or request changes');
        Example::create(
            $workflow,
            scenario: 'Reviewing a refactoring PR',
            input: 'PR that changes internal implementation without changing behavior',
            output: 'Verify tests still pass, check for performance regressions, approve if clean',
            explanation: 'Refactoring PRs need extra attention to behavioral preservation.',
        );
        Example::create(
            $workflow,
            scenario: 'Reviewing a bug fix',
            input: 'PR fixing a production bug',
            output: 'Verify the fix addresses root cause, check for regression tests, test edge cases',
        );
        $this->save($workflow);

        // Draft - Database Migration (created 1 month ago)
        $this->at('-1 month');
        $workflow = $this->draft('workflow-draft-db-migration', 'Database Migration', 'Safe database schema changes.');
        $workflow->updateContent('Follow this workflow for zero-downtime database migrations.');
        Step::create($workflow, 'Generate migration file');
        Step::create($workflow, 'Review generated SQL');
        Step::create($workflow, 'Test migration on staging');
        Step::create($workflow, 'Create rollback plan');
        Step::create($workflow, 'Execute in production during low traffic');
        Example::create(
            $workflow,
            scenario: 'Adding a new column',
            input: 'Need to add email_verified column to users table',
            output: 'Add nullable column first, then backfill data, then add NOT NULL constraint',
            explanation: 'Multi-step approach prevents locking issues.',
        );
        $this->save($workflow);

        // Draft - Incident Response (created 6 weeks ago, updated 2 weeks ago)
        $this->at('-6 weeks');
        $workflow = $this->draft('workflow-draft-incident', 'Incident Response', 'Handle production incidents.');
        $this->at('-2 weeks');
        $workflow->updateContent(
            "Emergency response workflow for production incidents.\n\n".
            "Priority: P1 = Critical, P2 = High, P3 = Medium"
        );
        Step::create($workflow, 'Acknowledge the incident and assign severity');
        Step::create($workflow, 'Communicate status to stakeholders');
        Step::create($workflow, 'Investigate root cause');
        Step::create($workflow, 'Implement fix or mitigation');
        Step::create($workflow, 'Verify resolution');
        Step::create($workflow, 'Write post-mortem');
        $this->save($workflow);

        // Draft - API Design (created 2 months ago)
        $this->at('-2 months');
        $workflow = $this->draft('workflow-draft-api-design', 'API Design', 'Design RESTful APIs.');
        $workflow->updateContent('Workflow for designing consistent REST APIs.');
        Step::create($workflow, 'Define resource models');
        Step::create($workflow, 'Plan URL structure');
        Step::create($workflow, 'Document request/response schemas');
        Step::create($workflow, 'Review with team');
        Example::create(
            $workflow,
            scenario: 'Designing user management API',
            input: 'Need CRUD operations for users',
            output: 'GET /users, POST /users, GET /users/{id}, PUT /users/{id}, DELETE /users/{id}',
        );
        $this->save($workflow);

        // More draft workflows for testing
        $this->at('-4 days');
        $workflow = $this->draft('workflow-draft-testing', 'Test Writing', 'Writing effective tests.');
        $workflow->updateContent('Follow the AAA pattern: Arrange, Act, Assert.');
        Step::create($workflow, 'Identify test scenarios');
        Step::create($workflow, 'Write test setup (Arrange)');
        Step::create($workflow, 'Execute the action (Act)');
        Step::create($workflow, 'Verify results (Assert)');
        $this->save($workflow);

        $this->at('-8 days');
        $workflow = $this->draft('workflow-draft-security-review', 'Security Review', 'Security assessment workflow.');
        $workflow->updateContent('Checklist for security reviews.');
        Step::create($workflow, 'Check for injection vulnerabilities');
        Step::create($workflow, 'Verify authentication/authorization');
        Step::create($workflow, 'Review data validation');
        Step::create($workflow, 'Check sensitive data handling');
        $this->save($workflow);

        $this->at('-12 days');
        $workflow = $this->draft('workflow-draft-deployment', 'Deployment Process', 'Production deployment steps.');
        Step::create($workflow, 'Run final CI checks');
        Step::create($workflow, 'Create release tag');
        Step::create($workflow, 'Deploy to staging');
        Step::create($workflow, 'Smoke test staging');
        Step::create($workflow, 'Deploy to production');
        Step::create($workflow, 'Monitor for errors');
        $this->save($workflow);

        $this->at('-15 days');
        $workflow = $this->draft('workflow-draft-onboarding', 'Developer Onboarding', 'New developer setup.');
        $workflow->updateContent('Guide for onboarding new team members.');
        Step::create($workflow, 'Clone repository');
        Step::create($workflow, 'Install dependencies');
        Step::create($workflow, 'Configure environment');
        Step::create($workflow, 'Run tests locally');
        Step::create($workflow, 'Review architecture docs');
        $this->save($workflow);

        $this->at('-18 days');
        $workflow = $this->draft('workflow-draft-refactoring', 'Refactoring Process', 'Safe code refactoring.');
        Step::create($workflow, 'Ensure test coverage exists');
        Step::create($workflow, 'Make small, incremental changes');
        Step::create($workflow, 'Run tests after each change');
        Step::create($workflow, 'Review for behavior preservation');
        $this->save($workflow);

        $this->at('-22 days');
        $workflow = $this->draft('workflow-draft-performance', 'Performance Investigation', 'Diagnose performance issues.');
        $workflow->updateContent('Systematic approach to performance debugging.');
        Step::create($workflow, 'Define performance baseline');
        Step::create($workflow, 'Profile application');
        Step::create($workflow, 'Identify bottlenecks');
        Step::create($workflow, 'Implement optimizations');
        Step::create($workflow, 'Measure improvements');
        $this->save($workflow);

        $this->at('-25 days');
        $workflow = $this->draft('workflow-draft-documentation', 'Documentation Update', 'Keep docs in sync.');
        Step::create($workflow, 'Identify affected documentation');
        Step::create($workflow, 'Update relevant sections');
        Step::create($workflow, 'Add code examples');
        Step::create($workflow, 'Review for accuracy');
        $this->save($workflow);

        $this->at('-28 days');
        $workflow = $this->draft('workflow-draft-release', 'Release Process', 'Version release workflow.');
        $workflow->updateContent('Steps for releasing a new version.');
        Step::create($workflow, 'Update changelog');
        Step::create($workflow, 'Bump version number');
        Step::create($workflow, 'Create release branch');
        Step::create($workflow, 'Run full test suite');
        Step::create($workflow, 'Tag release');
        Step::create($workflow, 'Deploy and announce');
        $this->save($workflow);

        $this->at('-32 days');
        $workflow = $this->draft('workflow-draft-hotfix', 'Hotfix Process', 'Emergency fix deployment.');
        Step::create($workflow, 'Create hotfix branch from production');
        Step::create($workflow, 'Implement minimal fix');
        Step::create($workflow, 'Test thoroughly');
        Step::create($workflow, 'Deploy immediately');
        Step::create($workflow, 'Backport to main branch');
        $this->save($workflow);

        $this->at('-35 days');
        $workflow = $this->draft('workflow-draft-accessibility', 'Accessibility Audit', 'A11y compliance check.');
        $workflow->updateContent('Ensure WCAG compliance.');
        Step::create($workflow, 'Run automated accessibility scanner');
        Step::create($workflow, 'Test keyboard navigation');
        Step::create($workflow, 'Verify screen reader compatibility');
        Step::create($workflow, 'Check color contrast');
        $this->save($workflow);
    }

    private function createPublishedWorkflows(): void
    {
        // Published - Sprint Planning (created 3 months ago, published 2 months ago)
        $this->at('-3 months');
        $workflow = $this->draft('workflow-published-sprint-planning', 'Sprint Planning', 'Agile sprint planning process.');
        $workflow->updateContent('Standard workflow for bi-weekly sprint planning sessions.');
        Step::create($workflow, 'Review previous sprint retrospective');
        Step::create($workflow, 'Groom and estimate backlog items');
        Step::create($workflow, 'Commit to sprint goals');
        Step::create($workflow, 'Break down stories into tasks');
        $this->at('-2 months');
        $workflow->publish();
        $this->save($workflow);

        // Published - Code Merge (created 4 months ago, published 3 months ago)
        $this->at('-4 months');
        $workflow = $this->draft('workflow-published-code-merge', 'Code Merge Process', 'Safe branch merging.');
        $workflow->updateContent('Follow this process to merge code safely.');
        Step::create($workflow, 'Ensure all CI checks pass');
        Step::create($workflow, 'Get required approvals');
        Step::create($workflow, 'Resolve any merge conflicts');
        Step::create($workflow, 'Squash commits if needed');
        Step::create($workflow, 'Merge and delete branch');
        Example::create(
            $workflow,
            scenario: 'Merging feature branch',
            input: 'Feature branch with 15 commits ready for merge',
            output: 'Squash to single commit, merge to main, delete feature branch',
        );
        $this->at('-3 months');
        $workflow->publish();
        $this->save($workflow);

        // Published - Issue Triage (created 5 months ago, published 4 months ago)
        $this->at('-5 months');
        $workflow = $this->draft('workflow-published-issue-triage', 'Issue Triage', 'Process new issues efficiently.');
        $workflow->updateContent('Daily workflow for triaging incoming issues.');
        Step::create($workflow, 'Review new issues');
        Step::create($workflow, 'Assign priority and labels');
        Step::create($workflow, 'Request more info if needed');
        Step::create($workflow, 'Assign to appropriate team');
        $this->at('-4 months');
        $workflow->publish();
        $this->save($workflow);

        // Published - Technical Debt (created 6 months ago, published 5 months ago)
        $this->at('-6 months');
        $workflow = $this->draft('workflow-published-tech-debt', 'Technical Debt Cleanup', 'Address accumulated debt.');
        $workflow->updateContent('Process for systematically reducing technical debt.');
        Step::create($workflow, 'Identify debt items');
        Step::create($workflow, 'Prioritize by impact');
        Step::create($workflow, 'Allocate time in sprint');
        Step::create($workflow, 'Implement improvements');
        Step::create($workflow, 'Document lessons learned');
        $this->at('-5 months');
        $workflow->publish();
        $this->save($workflow);

        // Published - Dependency Update (created 4 months ago, published 3.5 months ago)
        $this->at('-4 months');
        $workflow = $this->draft('workflow-published-deps-update', 'Dependency Updates', 'Keep dependencies current.');
        $workflow->updateContent('Regular workflow for updating project dependencies.');
        Step::create($workflow, 'Check for outdated dependencies');
        Step::create($workflow, 'Review changelogs for breaking changes');
        Step::create($workflow, 'Update in small batches');
        Step::create($workflow, 'Run full test suite');
        Step::create($workflow, 'Test critical paths manually');
        Example::create(
            $workflow,
            scenario: 'Major version update',
            input: 'React 18 to React 19 upgrade',
            output: 'Create dedicated branch, update gradually, test each component',
        );
        $this->at('-3 months -15 days');
        $workflow->publish();
        $this->save($workflow);

        // Published - Pair Programming (created 3 months ago, published 2.5 months ago)
        $this->at('-3 months');
        $workflow = $this->draft('workflow-published-pair-programming', 'Pair Programming', 'Effective pairing sessions.');
        $workflow->updateContent('Guidelines for productive pair programming.');
        Step::create($workflow, 'Define session goals');
        Step::create($workflow, 'Set up shared environment');
        Step::create($workflow, 'Rotate driver/navigator roles');
        Step::create($workflow, 'Take regular breaks');
        Step::create($workflow, 'Review and reflect');
        $this->at('-2 months -15 days');
        $workflow->publish();
        $this->save($workflow);

        // Published - Design Review (created 5 months ago, published 4.5 months ago)
        $this->at('-5 months');
        $workflow = $this->draft('workflow-published-design-review', 'Design Review', 'Evaluate design proposals.');
        $workflow->updateContent('Process for reviewing technical design documents.');
        Step::create($workflow, 'Read design document thoroughly');
        Step::create($workflow, 'Identify potential issues');
        Step::create($workflow, 'Propose alternatives if needed');
        Step::create($workflow, 'Document decisions');
        $this->at('-4 months -15 days');
        $workflow->publish();
        $this->save($workflow);

        // Published - Load Testing (created 4 months ago, published 3 months ago)
        $this->at('-4 months');
        $workflow = $this->draft('workflow-published-load-testing', 'Load Testing', 'Performance validation.');
        $workflow->updateContent('Workflow for conducting load tests.');
        Step::create($workflow, 'Define test scenarios');
        Step::create($workflow, 'Configure test environment');
        Step::create($workflow, 'Execute load tests');
        Step::create($workflow, 'Analyze results');
        Step::create($workflow, 'Report findings');
        Example::create(
            $workflow,
            scenario: 'API endpoint load test',
            input: '1000 concurrent users hitting /api/users endpoint',
            output: 'Response time < 200ms at p95, error rate < 0.1%',
        );
        $this->at('-3 months');
        $workflow->publish();
        $this->save($workflow);

        // Published - Knowledge Sharing (created 2 months ago, published 6 weeks ago)
        $this->at('-2 months');
        $workflow = $this->draft('workflow-published-knowledge-sharing', 'Knowledge Sharing', 'Team learning sessions.');
        $workflow->updateContent('Process for organizing knowledge sharing sessions.');
        Step::create($workflow, 'Choose topic and presenter');
        Step::create($workflow, 'Prepare materials');
        Step::create($workflow, 'Schedule session');
        Step::create($workflow, 'Record session');
        Step::create($workflow, 'Share resources');
        $this->at('-6 weeks');
        $workflow->publish();
        $this->save($workflow);

        // Published - Data Backup (created 3 months ago, published 2.5 months ago)
        $this->at('-3 months');
        $workflow = $this->draft('workflow-published-data-backup', 'Data Backup Process', 'Regular data backup.');
        $workflow->updateContent('Automated backup verification workflow.');
        Step::create($workflow, 'Verify backup job completion');
        Step::create($workflow, 'Test restore process');
        Step::create($workflow, 'Document any issues');
        Step::create($workflow, 'Report backup status');
        $this->at('-2 months -15 days');
        $workflow->publish();
        $this->save($workflow);
    }

    private function createArchivedWorkflows(): void
    {
        // Archived - Old deployment (created 8 months ago, archived 2 months ago)
        $this->at('-8 months');
        $workflow = $this->draft('workflow-archived-old-deployment', 'Legacy Deployment', 'Old deployment process.');
        $workflow->updateContent('Manual deployment steps (DEPRECATED: Use CI/CD instead).');
        Step::create($workflow, 'SSH to production server');
        Step::create($workflow, 'Pull latest code');
        Step::create($workflow, 'Restart services');
        $this->at('-2 months');
        $workflow->archive();
        $this->save($workflow);

        // Archived - Manual testing (created 10 months ago, archived 4 months ago)
        $this->at('-10 months');
        $workflow = $this->draft('workflow-archived-manual-testing', 'Manual Test Plan', 'Manual QA workflow.');
        $workflow->updateContent('Manual testing checklist (DEPRECATED: Use automated tests).');
        Step::create($workflow, 'Test login flow manually');
        Step::create($workflow, 'Check all form validations');
        Step::create($workflow, 'Verify email notifications');
        $this->at('-4 months');
        $workflow->archive();
        $this->save($workflow);

        // Archived - Was published then archived (created 1 year ago, published 10 months ago, archived 3 months ago)
        $this->at('-1 year');
        $workflow = $this->draft('workflow-archived-svn-workflow', 'SVN Workflow', 'Subversion version control.');
        $workflow->updateContent('SVN commit and merge workflow.');
        Step::create($workflow, 'svn update');
        Step::create($workflow, 'svn commit');
        Step::create($workflow, 'svn merge');
        $this->at('-10 months');
        $workflow->publish();
        $this->at('-3 months');
        $workflow->archive();
        $this->save($workflow);
    }

    private function createDeletedWorkflows(): void
    {
        // Deleted - Simple (created 6 months ago, deleted 1 month ago)
        $this->at('-6 months');
        $workflow = $this->draft('workflow-deleted-invalid', 'Invalid Workflow', 'This workflow is no longer valid.');
        $workflow->updateContent('This workflow is no longer valid.');
        $this->at('-1 month');
        $workflow->delete();
        $this->save($workflow);

        // Deleted - With steps (created 7 months ago, deleted 2 months ago)
        $this->at('-7 months');
        $workflow = $this->draft('workflow-deleted-skip-tests', 'Skip Tests', 'Deploy without testing.');
        $workflow->updateContent('Quick deployment by skipping tests (DANGEROUS).');
        Step::create($workflow, 'Push directly to main');
        Step::create($workflow, 'Deploy without CI');
        $this->at('-2 months');
        $workflow->delete();
        $this->save($workflow);

        // Deleted - Was published (created 9 months ago, published 8 months ago, deleted 1 month ago)
        $this->at('-9 months');
        $workflow = $this->draft('workflow-deleted-no-review', 'No Review Needed', 'Merge without review.');
        $workflow->updateContent('Merge PRs without code review (removed for security).');
        Step::create($workflow, 'Create PR');
        Step::create($workflow, 'Self-approve');
        Step::create($workflow, 'Merge immediately');
        $this->at('-8 months');
        $workflow->publish();
        $this->at('-1 month');
        $workflow->delete();
        $this->save($workflow);
    }

    private function at(string $modifier): void
    {
        Chronos::setTestNow(Chronos::now()->modify($modifier));
    }

    private function draft(string $id, string $name, string $description): Workflow
    {
        return Workflow::draft(DirectiveId::fromString($id), $name, $description);
    }

    private function save(Workflow $workflow): void
    {
        $this->manager->persist($workflow);
    }
}
