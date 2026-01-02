<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DataFixtures;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class RuleFixtures extends Fixture
{
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        try {
            $this->createDraftRules();
            $this->createPublishedRules();
            $this->createArchivedRules();
            $this->createDeletedRules();

            DomainEventQueue::reset();
            $manager->flush();
        } finally {
            Chronos::setTestNow(null);
        }
    }

    private function createDraftRules(): void
    {
        // Draft - no content, no examples (created 2 days ago)
        $this->at('-2 days');
        $rule = $this->draft('draft-empty', 'Empty Draft Rule', 'A new rule waiting for content.');
        $this->save($rule);

        // Draft - with content only (created 5 days ago, updated 1 day ago)
        $this->at('-5 days');
        $rule = $this->draft('draft-content-only', 'Variable Naming', 'Guidelines for naming variables.');
        $this->at('-1 day');
        $rule->updateContent('Always use meaningful variable names that describe their purpose.');
        $this->save($rule);

        // Draft - with one good example (created 1 week ago)
        $this->at('-1 week');
        $rule = $this->draft('draft-good-example', 'Descriptive Functions', 'Use descriptive function names.');
        $rule->updateContent('Function names should explain what the function does.');
        Example::create($rule, good: 'function calculateTotalPrice(items: Item[]): number');
        $this->save($rule);

        // Draft - with one bad example (created 10 days ago)
        $this->at('-10 days');
        $rule = $this->draft('draft-bad-example', 'Avoid Single Letters', 'Avoid single-letter variable names.');
        $rule->updateContent('Avoid single-letter variable names except for loop counters.');
        Example::create($rule, bad: 'const x = getUserData(); const y = processData(x);');
        $this->save($rule);

        // Draft - with complete example (created 2 weeks ago, updated 3 days ago)
        $this->at('-2 weeks');
        $rule = $this->draft('draft-complete-example', 'Early Returns', 'Use early returns to reduce nesting.');
        $rule->updateContent('Early returns improve readability by reducing indentation levels.');
        $this->at('-3 days');
        Example::create(
            $rule,
            good: "if (!user) return null;\nif (!user.isActive) return null;\nreturn user.profile;",
            bad: "if (user) {\n  if (user.isActive) {\n    return user.profile;\n  }\n}\nreturn null;",
            explanation: 'Early returns make the happy path more visible.',
        );
        $this->save($rule);

        // Draft - with multiple examples (created 3 weeks ago)
        $this->at('-3 weeks');
        $rule = $this->draft('draft-multiple-examples', 'Async/Await Usage', 'Prefer async/await over promise chains.');
        $rule->updateContent('Use async/await for better readability in asynchronous code.');
        Example::create(
            $rule,
            good: "const data = await fetchData();\nconst processed = await processData(data);",
            bad: 'fetchData().then(data => processData(data)).then(processed => ...);',
        );
        Example::create(
            $rule,
            good: "try {\n  const result = await riskyOperation();\n} catch (error) {\n  handleError(error);\n}",
            bad: 'riskyOperation().then(result => ...).catch(error => handleError(error));',
            explanation: 'Try/catch with async/await is more intuitive.',
        );
        $this->save($rule);

        // Draft - SQL Security (created 1 month ago)
        $this->at('-1 month');
        $rule = $this->draft('draft-sql-security', 'SQL Injection Prevention', 'Never concatenate user input into SQL.');
        $rule->updateContent('Use parameterized queries or an ORM to prevent SQL injection attacks.');
        Example::create(
            $rule,
            good: "\$stmt = \$pdo->prepare('SELECT * FROM users WHERE id = ?');\n\$stmt->execute([\$userId]);",
            bad: "\$query = \"SELECT * FROM users WHERE id = \" . \$_GET['id'];\n\$result = mysqli_query(\$conn, \$query);",
            explanation: 'The bad example is vulnerable to SQL injection.',
        );
        $this->save($rule);

        // Draft - Error Handling (created 6 weeks ago, updated 2 weeks ago)
        $this->at('-6 weeks');
        $rule = $this->draft('draft-error-handling', 'Error Handling Guide', 'Comprehensive error handling practices.');
        $this->at('-2 weeks');
        $rule->updateContent(
            "Error handling is critical for robust applications.\n\n".
            "1. Always catch errors at appropriate boundaries.\n".
            "2. Log errors with sufficient context for debugging.\n".
            '3. Provide meaningful error messages to users.',
        );
        Example::create(
            $rule,
            good: "try {\n  await processOrder(order);\n} catch (error) {\n  logger.error('Order processing failed', { orderId: order.id, error });\n  throw new UserFacingError('Unable to process your order.');\n}",
            bad: "try {\n  await processOrder(order);\n} catch (e) {\n  console.log(e);\n}",
        );
        $this->save($rule);

        // Draft - i18n (created 2 months ago)
        $this->at('-2 months');
        $rule = $this->draft('draft-i18n', 'Internationalization', 'Support for multiple languages.');
        $rule->updateContent('Always use Unicode-safe string operations and support RTL languages.');
        Example::create(
            $rule,
            good: "const greeting = t('hello', { name: user.name });",
            bad: "const greeting = 'Hello ' + user.name;",
            explanation: 'Hardcoded strings cannot be translated.',
        );
        $this->save($rule);

        // More drafts for testing
        $this->at('-4 days');
        $rule = $this->draft('draft-http-status', 'HTTP Status Codes', 'Return appropriate HTTP status codes.');
        $rule->updateContent('Use 2xx for success, 4xx for client errors, 5xx for server errors.');
        Example::create(
            $rule,
            good: 'return Response(status=201) // Created',
            bad: 'return Response(status=200) // for POST',
            explanation: '201 Created is more semantic for resource creation.',
        );
        $this->save($rule);

        $this->at('-8 days');
        $rule = $this->draft('draft-input-sanitization', 'Input Sanitization', 'Sanitize all user input.');
        $rule->updateContent('Never trust user input. Validate and sanitize everything.');
        Example::create($rule, good: 'const safe = DOMPurify.sanitize(userInput);');
        Example::create($rule, bad: 'element.innerHTML = userInput;', explanation: 'XSS vulnerability.');
        $this->save($rule);

        $this->at('-12 days');
        $rule = $this->draft('draft-aaa-pattern', 'AAA Test Pattern', 'Structure tests using Arrange-Act-Assert.');
        $rule->updateContent('Clearly separate setup, execution, and verification in tests.');
        Example::create(
            $rule,
            good: "// Arrange\nconst user = createUser();\n// Act\nconst result = service.process(user);\n// Assert\nexpect(result).toBe(true);",
            explanation: 'AAA pattern makes tests easier to read.',
        );
        $this->save($rule);

        $this->at('-15 days');
        $rule = $this->draft('draft-test-isolation', 'Test Isolation', 'Ensure tests are independent.');
        $rule->updateContent('Each test should run independently without shared state.');
        Example::create($rule, bad: 'let sharedData; beforeAll(() => { sharedData = fetchData(); });');
        $this->save($rule);

        $this->at('-18 days');
        $rule = $this->draft('draft-transactions', 'Transaction Usage', 'Use transactions for related operations.');
        $rule->updateContent('Wrap multiple related operations in a transaction for consistency.');
        $this->save($rule);

        $this->at('-22 days');
        $rule = $this->draft('draft-dry', 'DRY Principle', "Don't Repeat Yourself.");
        $rule->updateContent('Extract common logic into reusable functions or modules.');
        Example::create(
            $rule,
            good: 'const tax = calculateTax(amount, rate); // reuse everywhere',
            bad: "const tax1 = amount * 0.2;\nconst tax2 = amount * 0.2;",
        );
        $this->save($rule);

        $this->at('-25 days');
        $rule = $this->draft('draft-inline-comments', 'Inline Comments', 'Write comments that explain why.');
        $rule->updateContent('Code should be self-documenting. Comments explain intent.');
        Example::create(
            $rule,
            good: "// Offset by 1 because API uses 1-based indexing\nconst page = userPage - 1;",
            bad: "// subtract 1 from page\nconst page = userPage - 1;",
        );
        $this->save($rule);

        $this->at('-28 days');
        $rule = $this->draft('draft-atomic-commits', 'Atomic Commits', 'Each commit = one logical change.');
        $rule->updateContent("Don't mix unrelated changes in a single commit.");
        Example::create(
            $rule,
            good: "git commit -m 'feat: add login form'\ngit commit -m 'fix: correct validation'",
            bad: "git commit -m 'add login and fix stuff and update readme'",
        );
        $this->save($rule);

        $this->at('-32 days');
        $rule = $this->draft('draft-caching', 'Caching Strategy', 'Implement appropriate caching.');
        $rule->updateContent('Cache expensive computations, API responses, and database queries.');
        $this->save($rule);

        $this->at('-35 days');
        $rule = $this->draft('draft-semantic-html', 'Semantic HTML', 'Use semantic elements appropriately.');
        $rule->updateContent('Use button, nav, main, article instead of generic divs.');
        Example::create($rule, good: '<button onClick={handleClick}>Submit</button>');
        Example::create($rule, bad: '<div onClick={handleClick}>Submit</div>');
        $this->save($rule);
    }

    private function createPublishedRules(): void
    {
        // Published - API Documentation (created 3 months ago, published 2 months ago)
        $this->at('-3 months');
        $rule = $this->draft('published-api-docs', 'API Documentation', 'Document all public APIs.');
        $rule->updateContent('All public APIs must be documented with JSDoc or TSDoc comments.');
        $this->at('-2 months');
        $rule->publish();
        $this->save($rule);

        // Published - TypeScript Strict (created 4 months ago, published 3 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-typescript-strict', 'TypeScript Strict Mode', 'Enable strict type checking.');
        $rule->updateContent('Use TypeScript strict mode for better type safety.');
        Example::create($rule, good: '// tsconfig.json\n{ "compilerOptions": { "strict": true } }');
        $this->at('-3 months');
        $rule->publish();
        $this->save($rule);

        // Published - localStorage Security (created 5 months ago, published 4 months ago)
        $this->at('-5 months');
        $rule = $this->draft('published-localstorage', 'Sensitive Data Storage', 'Never store sensitive data in localStorage.');
        $rule->updateContent('localStorage is accessible via XSS. Use httpOnly cookies for sensitive data.');
        Example::create(
            $rule,
            bad: "localStorage.setItem('authToken', token);\nlocalStorage.setItem('userPassword', password);",
            explanation: 'localStorage is accessible via XSS attacks.',
        );
        $this->at('-4 months');
        $rule->publish();
        $this->save($rule);

        // Published - Dependency Injection (created 6 months ago, published 5 months ago)
        $this->at('-6 months');
        $rule = $this->draft('published-di', 'Dependency Injection', 'Use DI for testability.');
        $rule->updateContent('Use dependency injection to improve testability and maintainability.');
        Example::create(
            $rule,
            good: "class UserService {\n  constructor(private readonly repo: UserRepository) {}\n}",
            bad: "class UserService {\n  private repo = new UserRepository();\n}",
            explanation: 'DI allows swapping implementations for testing.',
        );
        $this->at('-5 months');
        $rule->publish();
        $this->save($rule);

        // Published - REST API Naming (created 4 months ago, published 3.5 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-rest-naming', 'REST API Resource Naming', 'Use plural nouns for endpoints.');
        $rule->updateContent('REST endpoints should use plural nouns to represent collections.');
        Example::create($rule, good: 'GET /api/users', bad: 'GET /api/user');
        Example::create($rule, good: 'POST /api/orders', bad: 'POST /api/order');
        $this->at('-3 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - HTTP Methods (created 3 months ago, published 2.5 months ago)
        $this->at('-3 months');
        $rule = $this->draft('published-http-methods', 'HTTP Methods Usage', 'Use appropriate HTTP methods.');
        $rule->updateContent('GET for reading, POST for creating, PUT for updating, DELETE for removing.');
        Example::create($rule, good: 'PUT /api/users/123', bad: 'POST /api/users/123/update');
        $this->at('-2 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Password Hashing (created 5 months ago, published 4.5 months ago)
        $this->at('-5 months');
        $rule = $this->draft('published-password-hashing', 'Password Hashing', 'Always hash passwords.');
        $rule->updateContent('Use bcrypt, Argon2, or similar algorithms. Never store plaintext passwords.');
        Example::create(
            $rule,
            good: 'const hash = await bcrypt.hash(password, 12);',
            bad: "db.query('INSERT INTO users (password) VALUES (?)', [password]);",
            explanation: 'Plaintext passwords are a critical vulnerability.',
        );
        $this->at('-4 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - CORS Configuration (created 4 months ago, published 3 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-cors', 'CORS Configuration', 'Configure CORS properly.');
        $rule->updateContent('Restrict allowed origins, methods, and headers in production.');
        Example::create(
            $rule,
            good: 'Access-Control-Allow-Origin: https://myapp.com',
            bad: 'Access-Control-Allow-Origin: *',
            explanation: 'Wildcard CORS allows any origin to access your API.',
        );
        $this->at('-3 months');
        $rule->publish();
        $this->save($rule);

        // Published - Test Naming (created 2 months ago, published 6 weeks ago)
        $this->at('-2 months');
        $rule = $this->draft('published-test-naming', 'Test Method Naming', 'Use descriptive test names.');
        $rule->updateContent('Test names should describe the scenario and expected outcome.');
        Example::create(
            $rule,
            good: 'it("should return 404 when user not found")',
            bad: 'it("test1")',
        );
        $this->at('-6 weeks');
        $rule->publish();
        $this->save($rule);

        // Published - Database Indexes (created 3 months ago, published 2.5 months ago)
        $this->at('-3 months');
        $rule = $this->draft('published-db-indexes', 'Database Indexes', 'Create indexes for frequently queried columns.');
        $rule->updateContent('Add indexes to columns used in WHERE, JOIN, and ORDER BY clauses.');
        Example::create($rule, good: 'CREATE INDEX idx_users_email ON users(email);');
        $this->at('-2 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Database Migrations (created 4 months ago, published 3.5 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-migrations', 'Database Migrations', 'Use migrations for schema changes.');
        $rule->updateContent('Never modify database schema directly. Use versioned migrations.');
        Example::create(
            $rule,
            good: 'php bin/console doctrine:migrations:diff',
            bad: 'ALTER TABLE users ADD COLUMN age INT; -- run manually',
        );
        $this->at('-3 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Single Responsibility (created 5 months ago, published 4 months ago)
        $this->at('-5 months');
        $rule = $this->draft('published-srp', 'Single Responsibility', 'Each class = one reason to change.');
        $rule->updateContent('Break down large classes into smaller, focused components.');
        Example::create(
            $rule,
            bad: 'class User { save() {} sendEmail() {} generateReport() {} }',
            explanation: 'This class has multiple responsibilities.',
        );
        $this->at('-4 months');
        $rule->publish();
        $this->save($rule);

        // Published - Magic Numbers (created 3 months ago, published 2 months ago)
        $this->at('-3 months');
        $rule = $this->draft('published-magic-numbers', 'Avoid Magic Numbers', 'Use named constants.');
        $rule->updateContent('Constants make code more readable and maintainable.');
        Example::create(
            $rule,
            good: "const MAX_LOGIN_ATTEMPTS = 3;\nif (attempts > MAX_LOGIN_ATTEMPTS)",
            bad: 'if (attempts > 3)',
        );
        $this->at('-2 months');
        $rule->publish();
        $this->save($rule);

        // Published - README (created 6 months ago, published 5.5 months ago)
        $this->at('-6 months');
        $rule = $this->draft('published-readme', 'README Requirements', 'Every project needs a README.');
        $rule->updateContent('Include: description, installation, usage, contributing guidelines.');
        $this->at('-5 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Branch Naming (created 4 months ago, published 3.5 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-branch-naming', 'Branch Naming Convention', 'Use consistent branch names.');
        $rule->updateContent('Format: type/issue-short-description (e.g., feat/dai-123-add-auth)');
        Example::create($rule, good: 'feat/dai-123-user-authentication', bad: 'my-branch');
        $this->at('-3 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Lazy Loading (created 2 months ago, published 6 weeks ago)
        $this->at('-2 months');
        $rule = $this->draft('published-lazy-loading', 'Lazy Loading', 'Load resources only when needed.');
        $rule->updateContent('Defer loading of non-critical resources to improve initial load time.');
        Example::create($rule, good: 'const Component = lazy(() => import("./HeavyComponent"));');
        $this->at('-6 weeks');
        $rule->publish();
        $this->save($rule);

        // Published - N+1 Queries (created 3 months ago, published 2.5 months ago)
        $this->at('-3 months');
        $rule = $this->draft('published-n-plus-one', 'Avoid N+1 Queries', 'Prevent N+1 query problems.');
        $rule->updateContent('Use eager loading or batch queries instead of querying in loops.');
        Example::create(
            $rule,
            good: "users = User.query().with('posts').all()",
            bad: "for user in users:\n    user.posts  # triggers query each iteration",
            explanation: 'N+1 causes performance degradation at scale.',
        );
        $this->at('-2 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Alt Text (created 4 months ago, published 3 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-alt-text', 'Image Alt Text', 'Provide meaningful alt text.');
        $rule->updateContent('Alt text should describe the image content for screen readers.');
        Example::create(
            $rule,
            good: '<img src="chart.png" alt="Monthly revenue chart showing 20% growth">',
            bad: '<img src="chart.png" alt="image">',
        );
        $this->at('-3 months');
        $rule->publish();
        $this->save($rule);

        // Published - Code Style (created 5 months ago, published 4 months ago)
        $this->at('-5 months');
        $rule = $this->draft('published-code-style', 'Naming Conventions', 'Consistent naming across codebase.');
        $rule->updateContent('Maintain consistent naming conventions throughout the project.');
        Example::create($rule, good: 'const userName = "john";', bad: 'const user_name = "john";');
        Example::create($rule, good: 'function getUserById(id)', bad: 'function get_user_by_id(id)');
        Example::create($rule, good: 'class UserService', bad: 'class user_service');
        Example::create($rule, good: 'const MAX_RETRY_COUNT = 3;', bad: 'const maxRetryCount = 3;', explanation: 'Constants should be SCREAMING_SNAKE_CASE.');
        $this->at('-4 months');
        $rule->publish();
        $this->save($rule);

        // Published - Const vs Let (created 3 months ago, published 2.5 months ago)
        $this->at('-3 months');
        $rule = $this->draft('published-const-let', 'Const Over Let', 'Prefer const when value won\'t change.');
        $rule->updateContent('Using const signals intent that the value should not be reassigned.');
        Example::create(
            $rule,
            good: "const MAX_RETRIES = 3;\nconst users = fetchUsers();",
            bad: "let MAX_RETRIES = 3;\nlet users = fetchUsers();",
            explanation: 'const makes code easier to reason about.',
        );
        $this->at('-2 months -15 days');
        $rule->publish();
        $this->save($rule);

        // Published - Commit Messages (created 4 months ago, published 3 months ago)
        $this->at('-4 months');
        $rule = $this->draft('published-commit-messages', 'Commit Messages', 'Explain why, not just what.');
        $rule->updateContent('Use meaningful commit messages that provide context for changes.');
        Example::create($rule, good: 'fix(auth): prevent session hijacking by validating token origin');
        Example::create($rule, bad: 'fixed bug', explanation: 'Provides no context about what was fixed.');
        Example::create(
            $rule,
            good: 'feat(api): add rate limiting to prevent abuse (closes #123)',
            bad: 'added rate limiting',
            explanation: 'Reference issues when applicable.',
        );
        $this->at('-3 months');
        $rule->publish();
        $this->save($rule);

        // Published - Input Validation (created 5 months ago, published 4 months ago)
        $this->at('-5 months');
        $rule = $this->draft('published-input-validation', 'Input Validation', 'Handle all edge cases.');
        $rule->updateContent('Validate input thoroughly before processing.');
        Example::create(
            $rule,
            good: "function validateEmail(email: string): boolean {\n  if (!email) return false;\n  if (email.length > 254) return false;\n  return EMAIL_REGEX.test(email);\n}",
            bad: "function validateEmail(email: string): boolean {\n  return EMAIL_REGEX.test(email);\n}",
            explanation: 'Check for null/undefined and length limits.',
        );
        Example::create(
            $rule,
            good: "function parseInt(value: string): number | null {\n  const num = Number(value);\n  return Number.isNaN(num) ? null : num;\n}",
            bad: "function parseInt(value: string): number {\n  return Number(value);\n}",
        );
        $this->at('-4 months');
        $rule->publish();
        $this->save($rule);
    }

    private function createArchivedRules(): void
    {
        // Archived - jQuery (created 8 months ago, archived 2 months ago)
        $this->at('-8 months');
        $rule = $this->draft('archived-jquery', 'jQuery for DOM', 'Use jQuery for DOM manipulation.');
        $rule->updateContent('Use jQuery for DOM manipulation. (DEPRECATED: Use modern frameworks instead)');
        $this->at('-2 months');
        $rule->archive();
        $this->save($rule);

        // Archived - Callbacks (created 10 months ago, archived 4 months ago)
        $this->at('-10 months');
        $rule = $this->draft('archived-callbacks', 'Callback Pattern', 'Use callbacks for async operations.');
        $rule->updateContent('Use callbacks for asynchronous operations.');
        Example::create(
            $rule,
            good: "fetchData(function(err, data) {\n  if (err) handleError(err);\n  else processData(data);\n});",
            explanation: 'This pattern was standard before Promises. Now prefer async/await.',
        );
        $this->at('-4 months');
        $rule->archive();
        $this->save($rule);

        // Archived - Was published then archived (created 1 year ago, published 10 months ago, archived 3 months ago)
        $this->at('-1 year');
        $rule = $this->draft('archived-var', 'Use var Declaration', 'Use var for variable declarations.');
        $rule->updateContent('Use var for variable declarations.');
        Example::create($rule, bad: 'let x = 1; const y = 2;', explanation: 'This advice is outdated. Use let/const.');
        $this->at('-10 months');
        $rule->publish();
        $this->at('-3 months');
        $rule->archive();
        $this->save($rule);
    }

    private function createDeletedRules(): void
    {
        // Deleted - Simple (created 6 months ago, deleted 1 month ago)
        $this->at('-6 months');
        $rule = $this->draft('deleted-invalid', 'Invalid Content', 'This content is no longer valid.');
        $rule->updateContent('This content is no longer valid.');
        $this->at('-1 month');
        $rule->delete();
        $this->save($rule);

        // Deleted - With examples (created 7 months ago, deleted 2 months ago)
        $this->at('-7 months');
        $rule = $this->draft('deleted-global-state', 'Global Variables', 'Use global variables for state.');
        $rule->updateContent('Use global variables for application state.');
        Example::create(
            $rule,
            good: 'window.APP_STATE = { user: null, theme: "dark" };',
            explanation: 'This advice is harmful.',
        );
        $this->at('-2 months');
        $rule->delete();
        $this->save($rule);

        // Deleted - Was published (created 9 months ago, published 8 months ago, deleted 1 month ago)
        $this->at('-9 months');
        $rule = $this->draft('deleted-no-typescript', 'Avoid TypeScript', 'Use plain JavaScript instead.');
        $rule->updateContent('Avoid using TypeScript, use plain JavaScript instead.');
        Example::create($rule, bad: 'const x: number = 1;', explanation: 'Bad advice that was removed.');
        $this->at('-8 months');
        $rule->publish();
        $this->at('-1 month');
        $rule->delete();
        $this->save($rule);
    }

    private function at(string $modifier): void
    {
        Chronos::setTestNow(Chronos::now()->modify($modifier));
    }

    private function draft(string $id, string $name, string $description): Rule
    {
        return Rule::draft(DirectiveId::fromString($id), $name, $description);
    }

    private function save(Rule $rule): void
    {
        $this->manager->persist($rule);
    }
}
