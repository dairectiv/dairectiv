<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Doctrine\DataFixtures;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class RuleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // === DRAFT RULES ===

        // Draft without examples, without content
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-no-examples-no-content'),
            'Draft Rule Without Examples',
            'A simple draft rule with no examples and no content defined yet.',
        );
        $manager->persist($rule);

        // Draft without examples, with content
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-no-examples-with-content'),
            'Draft Rule With Content Only',
            'A draft rule that has content but no examples to illustrate usage.',
        );
        $rule->updateContent('Always use meaningful variable names that describe their purpose.');
        $manager->persist($rule);

        // Draft with 1 example (good only)
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-one-example-good-only'),
            'Draft Rule With Good Example',
            'A draft rule demonstrating a single good example.',
        );
        $rule->updateContent('Use descriptive function names that explain what the function does.');
        Example::create($rule, good: 'function calculateTotalPrice(items: Item[]): number');
        $manager->persist($rule);

        // Draft with 1 example (bad only)
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-one-example-bad-only'),
            'Draft Rule With Bad Example',
            'A draft rule demonstrating what not to do.',
        );
        $rule->updateContent('Avoid single-letter variable names except for loop counters.');
        Example::create($rule, bad: 'const x = getUserData(); const y = processData(x);');
        $manager->persist($rule);

        // Draft with 1 example (good and bad)
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-one-example-good-bad'),
            'Draft Rule Good and Bad',
            'A draft rule showing both correct and incorrect approaches.',
        );
        $rule->updateContent('Use early returns to reduce nesting and improve readability.');
        Example::create(
            $rule,
            good: "if (!user) return null;\nif (!user.isActive) return null;\nreturn user.profile;",
            bad: "if (user) {\n  if (user.isActive) {\n    return user.profile;\n  }\n}\nreturn null;",
        );
        $manager->persist($rule);

        // Draft with 1 example (good, bad, and explanation)
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-one-example-full'),
            'Draft Rule Complete Example',
            'A draft rule with a complete example including explanation.',
        );
        $rule->updateContent('Prefer const over let when the variable will not be reassigned.');
        Example::create(
            $rule,
            good: "const MAX_RETRIES = 3;\nconst users = fetchUsers();",
            bad: "let MAX_RETRIES = 3;\nlet users = fetchUsers();",
            explanation: 'Using const signals intent that the value should not change, making code easier to reason about.',
        );
        $manager->persist($rule);

        // Draft with multiple examples (2)
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-two-examples'),
            'Draft Rule Multiple Examples',
            'A draft rule with two different examples.',
        );
        $rule->updateContent('Use async/await instead of promise chains for better readability.');
        Example::create(
            $rule,
            good: "const data = await fetchData();\nconst processed = await processData(data);",
            bad: "fetchData().then(data => processData(data)).then(processed => ...);",
        );
        Example::create(
            $rule,
            good: "try {\n  const result = await riskyOperation();\n} catch (error) {\n  handleError(error);\n}",
            bad: "riskyOperation().then(result => ...).catch(error => handleError(error));",
            explanation: 'Try/catch with async/await is more intuitive than .catch() chains.',
        );
        $manager->persist($rule);

        // Draft with multiple examples (3) - mixed
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-three-examples-mixed'),
            'Draft Rule Mixed Examples',
            'A draft rule with three examples of varying completeness.',
        );
        $rule->updateContent('Use meaningful commit messages that explain the why, not just the what.');
        Example::create($rule, good: 'fix(auth): prevent session hijacking by validating token origin');
        Example::create($rule, bad: 'fixed bug', explanation: 'This message provides no context about what was fixed or why.');
        Example::create(
            $rule,
            good: 'feat(api): add rate limiting to prevent abuse (closes #123)',
            bad: 'added rate limiting',
            explanation: 'Reference issues when applicable and explain the motivation.',
        );
        $manager->persist($rule);

        // === PUBLISHED RULES ===

        // Published without examples
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-no-examples'),
            'Published Rule No Examples',
            'A published rule that serves as a general guideline without specific examples.',
        );
        $rule->updateContent('All public APIs must be documented with JSDoc or TSDoc comments.');
        $rule->publish();
        $manager->persist($rule);

        // Published with 1 example (good only, no explanation)
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-good-example'),
            'Published Rule Good Example',
            'A published rule demonstrating best practices.',
        );
        $rule->updateContent('Use TypeScript strict mode for better type safety.');
        Example::create($rule, good: '// tsconfig.json\n{ "compilerOptions": { "strict": true } }');
        $rule->publish();
        $manager->persist($rule);

        // Published with 1 example (bad only, with explanation)
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-bad-example-explained'),
            'Published Rule Anti-Pattern',
            'A published rule highlighting an anti-pattern to avoid.',
        );
        $rule->updateContent('Never store sensitive data in localStorage.');
        Example::create(
            $rule,
            bad: "localStorage.setItem('authToken', token);\nlocalStorage.setItem('userPassword', password);",
            explanation: 'localStorage is accessible via XSS attacks. Use httpOnly cookies for sensitive data.',
        );
        $rule->publish();
        $manager->persist($rule);

        // Published with complete example
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-complete-example'),
            'Published Rule Complete',
            'A published rule with comprehensive documentation.',
        );
        $rule->updateContent('Use dependency injection to improve testability and maintainability.');
        Example::create(
            $rule,
            good: "class UserService {\n  constructor(private readonly repo: UserRepository) {}\n}",
            bad: "class UserService {\n  private repo = new UserRepository();\n}",
            explanation: 'DI allows swapping implementations for testing and follows SOLID principles.',
        );
        $rule->publish();
        $manager->persist($rule);

        // Published with multiple examples
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-multiple-examples'),
            'Published Rule Extensive',
            'A published rule with extensive examples covering various scenarios.',
        );
        $rule->updateContent('Handle all edge cases in input validation.');
        Example::create(
            $rule,
            good: "function validateEmail(email: string): boolean {\n  if (!email) return false;\n  if (email.length > 254) return false;\n  return EMAIL_REGEX.test(email);\n}",
            bad: "function validateEmail(email: string): boolean {\n  return EMAIL_REGEX.test(email);\n}",
            explanation: 'Always check for null/undefined and length limits before regex validation.',
        );
        Example::create(
            $rule,
            good: "function parseInt(value: string): number | null {\n  const num = Number(value);\n  return Number.isNaN(num) ? null : num;\n}",
            bad: "function parseInt(value: string): number {\n  return Number(value);\n}",
        );
        Example::create($rule, good: "const sanitized = input.trim().toLowerCase();");
        $rule->publish();
        $manager->persist($rule);

        // Published with no content but examples
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-examples-no-content'),
            'Published Rule Examples Only',
            'A published rule where examples speak for themselves.',
        );
        Example::create(
            $rule,
            good: 'const { data, error } = await fetchUser(id);',
            bad: 'const result = await fetchUser(id); // result could be anything',
            explanation: 'Destructuring makes the return type explicit at the call site.',
        );
        $rule->publish();
        $manager->persist($rule);

        // === ARCHIVED RULES ===

        // Archived without examples
        $rule = Rule::draft(
            DirectiveId::fromString('archived-rule-no-examples'),
            'Archived Rule Legacy',
            'An archived rule that is no longer relevant but kept for historical reference.',
        );
        $rule->updateContent('Use jQuery for DOM manipulation. (DEPRECATED: Use modern frameworks instead)');
        $rule->archive();
        $manager->persist($rule);

        // Archived with examples
        $rule = Rule::draft(
            DirectiveId::fromString('archived-rule-with-examples'),
            'Archived Rule Documented',
            'An archived rule with documentation preserved.',
        );
        $rule->updateContent('Use callbacks for asynchronous operations.');
        Example::create(
            $rule,
            good: "fetchData(function(err, data) {\n  if (err) handleError(err);\n  else processData(data);\n});",
            explanation: 'This pattern was standard before Promises. Now prefer async/await.',
        );
        $rule->archive();
        $manager->persist($rule);

        // Archived from published state
        $rule = Rule::draft(
            DirectiveId::fromString('archived-rule-was-published'),
            'Archived Previously Published',
            'A rule that was published and later archived.',
        );
        $rule->updateContent('Use var for variable declarations.');
        Example::create($rule, bad: 'let x = 1; const y = 2;', explanation: 'This advice is outdated. Use let/const.');
        $rule->publish();
        $rule->archive();
        $manager->persist($rule);

        // === DELETED RULES ===

        // Deleted without examples
        $rule = Rule::draft(
            DirectiveId::fromString('deleted-rule-no-examples'),
            'Deleted Rule Simple',
            'A deleted rule that was removed from the system.',
        );
        $rule->updateContent('This content is no longer valid.');
        $rule->delete();
        $manager->persist($rule);

        // Deleted with examples
        $rule = Rule::draft(
            DirectiveId::fromString('deleted-rule-with-examples'),
            'Deleted Rule With History',
            'A deleted rule that had examples before removal.',
        );
        $rule->updateContent('Use global variables for application state.');
        Example::create(
            $rule,
            good: 'window.APP_STATE = { user: null, theme: "dark" };',
            explanation: 'This advice is harmful and was deleted.',
        );
        $rule->delete();
        $manager->persist($rule);

        // Deleted from published state
        $rule = Rule::draft(
            DirectiveId::fromString('deleted-rule-was-published'),
            'Deleted Previously Published',
            'A rule that was published and later deleted.',
        );
        $rule->updateContent('Avoid using TypeScript, use plain JavaScript instead.');
        Example::create($rule, bad: "const x: number = 1;", explanation: 'Bad advice that was removed.');
        $rule->publish();
        $rule->delete();
        $manager->persist($rule);

        // === ADDITIONAL DRAFT RULES FOR VARIETY ===

        // Draft with only explanation
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-explanation-only'),
            'Draft Rule Explanation Focus',
            'A draft rule where the explanation is the main content.',
        );
        $rule->updateContent('Consider performance implications of your code.');
        Example::create(
            $rule,
            explanation: 'Always measure performance before and after optimizations. Premature optimization is the root of all evil.',
        );
        $manager->persist($rule);

        // Draft with long content
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-long-content'),
            'Draft Rule Comprehensive Guide',
            'A draft rule with detailed content explaining multiple aspects of the guideline.',
        );
        $rule->updateContent(
            "Error handling is critical for robust applications.\n\n" .
            "1. Always catch errors at appropriate boundaries.\n" .
            "2. Log errors with sufficient context for debugging.\n" .
            "3. Provide meaningful error messages to users.\n" .
            "4. Use error codes for programmatic handling.\n" .
            "5. Consider retry strategies for transient failures.",
        );
        Example::create(
            $rule,
            good: "try {\n  await processOrder(order);\n} catch (error) {\n  logger.error('Order processing failed', { orderId: order.id, error });\n  throw new UserFacingError('Unable to process your order. Please try again.');\n}",
            bad: "try {\n  await processOrder(order);\n} catch (e) {\n  console.log(e);\n}",
        );
        $manager->persist($rule);

        // Draft with special characters in content
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-special-chars'),
            'Draft Rule SQL Security',
            'A draft rule about SQL injection prevention.',
        );
        $rule->updateContent("Never concatenate user input directly into SQL queries. Use parameterized queries or an ORM.");
        Example::create(
            $rule,
            good: "\$stmt = \$pdo->prepare('SELECT * FROM users WHERE id = ?');\n\$stmt->execute([\$userId]);",
            bad: "\$query = \"SELECT * FROM users WHERE id = \" . \$_GET['id'];\n\$result = mysqli_query(\$conn, \$query);",
            explanation: "The bad example is vulnerable to SQL injection. An attacker could input: 1 OR 1=1",
        );
        $manager->persist($rule);

        // Published with many examples
        $rule = Rule::draft(
            DirectiveId::fromString('published-rule-many-examples'),
            'Published Rule Code Style',
            'A published rule about consistent code style.',
        );
        $rule->updateContent('Maintain consistent naming conventions across the codebase.');
        Example::create($rule, good: 'const userName = "john";', bad: 'const user_name = "john";');
        Example::create($rule, good: 'function getUserById(id)', bad: 'function get_user_by_id(id)');
        Example::create($rule, good: 'class UserService', bad: 'class user_service');
        Example::create($rule, good: 'const MAX_RETRY_COUNT = 3;', bad: 'const maxRetryCount = 3;', explanation: 'Constants should be SCREAMING_SNAKE_CASE.');
        Example::create($rule, good: 'interface UserRepository', bad: 'interface IUserRepository', explanation: 'Avoid Hungarian notation prefixes.');
        $rule->publish();
        $manager->persist($rule);

        // Draft with unicode content
        $rule = Rule::draft(
            DirectiveId::fromString('draft-rule-internationalization'),
            'Draft Rule i18n Support',
            'A draft rule about internationalization best practices.',
        );
        $rule->updateContent('Always use Unicode-safe string operations and support RTL languages.');
        Example::create(
            $rule,
            good: "const greeting = t('hello', { name: user.name }); // Outputs: مرحبا أحمد or Hello John",
            bad: "const greeting = 'Hello ' + user.name;",
            explanation: 'Hardcoded strings cannot be translated. Use i18n libraries.',
        );
        $manager->persist($rule);

        // === ADDITIONAL RULES FOR PAGINATION TESTING ===

        // API Design rules
        $rule = Rule::draft(
            DirectiveId::fromString('api-rule-rest-naming'),
            'REST API Resource Naming',
            'Use plural nouns for REST API resource endpoints.',
        );
        $rule->updateContent('REST endpoints should use plural nouns to represent collections.');
        Example::create($rule, good: 'GET /api/users', bad: 'GET /api/user');
        Example::create($rule, good: 'POST /api/orders', bad: 'POST /api/order');
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('api-rule-http-methods'),
            'HTTP Methods Usage',
            'Use appropriate HTTP methods for CRUD operations.',
        );
        $rule->updateContent('GET for reading, POST for creating, PUT for updating, DELETE for removing.');
        Example::create($rule, good: 'PUT /api/users/123', bad: 'POST /api/users/123/update');
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('api-rule-status-codes'),
            'HTTP Status Codes',
            'Return appropriate HTTP status codes for API responses.',
        );
        $rule->updateContent('Use 2xx for success, 4xx for client errors, 5xx for server errors.');
        Example::create(
            $rule,
            good: 'return Response(status=201) // Created',
            bad: 'return Response(status=200) // for POST that creates',
            explanation: '201 Created is more semantic for resource creation.',
        );
        $manager->persist($rule);

        // Security rules
        $rule = Rule::draft(
            DirectiveId::fromString('security-rule-password-hashing'),
            'Password Hashing',
            'Always hash passwords before storing them.',
        );
        $rule->updateContent('Use bcrypt, Argon2, or similar algorithms. Never store plaintext passwords.');
        Example::create(
            $rule,
            good: "const hash = await bcrypt.hash(password, 12);",
            bad: "db.query('INSERT INTO users (password) VALUES (?)', [password]);",
            explanation: 'Plaintext passwords are a critical security vulnerability.',
        );
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('security-rule-input-sanitization'),
            'Input Sanitization',
            'Sanitize all user input before processing.',
        );
        $rule->updateContent('Never trust user input. Validate and sanitize everything.');
        Example::create($rule, good: "const safe = DOMPurify.sanitize(userInput);");
        Example::create($rule, bad: "element.innerHTML = userInput;", explanation: 'XSS vulnerability.');
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('security-rule-cors'),
            'CORS Configuration',
            'Configure CORS headers properly for API security.',
        );
        $rule->updateContent('Restrict allowed origins, methods, and headers in production.');
        Example::create(
            $rule,
            good: "Access-Control-Allow-Origin: https://myapp.com",
            bad: "Access-Control-Allow-Origin: *",
            explanation: 'Wildcard CORS allows any origin to access your API.',
        );
        $rule->publish();
        $manager->persist($rule);

        // Testing rules
        $rule = Rule::draft(
            DirectiveId::fromString('testing-rule-test-naming'),
            'Test Method Naming',
            'Use descriptive names for test methods.',
        );
        $rule->updateContent('Test names should describe the scenario and expected outcome.');
        Example::create(
            $rule,
            good: 'it("should return 404 when user not found")',
            bad: 'it("test1")',
        );
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('testing-rule-aaa-pattern'),
            'AAA Test Pattern',
            'Structure tests using Arrange-Act-Assert pattern.',
        );
        $rule->updateContent('Clearly separate setup, execution, and verification in tests.');
        Example::create(
            $rule,
            good: "// Arrange\nconst user = createUser();\n// Act\nconst result = service.process(user);\n// Assert\nexpect(result).toBe(true);",
            explanation: 'AAA pattern makes tests easier to read and maintain.',
        );
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('testing-rule-mock-isolation'),
            'Test Isolation',
            'Ensure tests are isolated and independent.',
        );
        $rule->updateContent('Each test should be able to run independently without shared state.');
        Example::create($rule, bad: "let sharedData; beforeAll(() => { sharedData = fetchData(); });");
        $manager->persist($rule);

        // Database rules
        $rule = Rule::draft(
            DirectiveId::fromString('database-rule-indexes'),
            'Database Indexes',
            'Create indexes for frequently queried columns.',
        );
        $rule->updateContent('Add indexes to columns used in WHERE, JOIN, and ORDER BY clauses.');
        Example::create($rule, good: 'CREATE INDEX idx_users_email ON users(email);');
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('database-rule-migrations'),
            'Database Migrations',
            'Use migrations for schema changes.',
        );
        $rule->updateContent('Never modify database schema directly. Use versioned migrations.');
        Example::create(
            $rule,
            good: 'php bin/console doctrine:migrations:diff',
            bad: 'ALTER TABLE users ADD COLUMN age INT; -- run manually',
        );
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('database-rule-transactions'),
            'Transaction Usage',
            'Use transactions for related database operations.',
        );
        $rule->updateContent('Wrap multiple related operations in a transaction to ensure consistency.');
        $manager->persist($rule);

        // Code Quality rules
        $rule = Rule::draft(
            DirectiveId::fromString('code-rule-single-responsibility'),
            'Single Responsibility Principle',
            'Each class/function should have one reason to change.',
        );
        $rule->updateContent('Break down large classes into smaller, focused components.');
        Example::create(
            $rule,
            bad: "class User { save() {} sendEmail() {} generateReport() {} }",
            explanation: 'This class has multiple responsibilities.',
        );
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('code-rule-dry'),
            'DRY Principle',
            'Don\'t Repeat Yourself - avoid code duplication.',
        );
        $rule->updateContent('Extract common logic into reusable functions or modules.');
        Example::create(
            $rule,
            good: "const tax = calculateTax(amount, rate);\n// reuse everywhere",
            bad: "const tax1 = amount * 0.2;\nconst tax2 = amount * 0.2;",
        );
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('code-rule-magic-numbers'),
            'Avoid Magic Numbers',
            'Use named constants instead of magic numbers.',
        );
        $rule->updateContent('Constants make code more readable and maintainable.');
        Example::create(
            $rule,
            good: "const MAX_LOGIN_ATTEMPTS = 3;\nif (attempts > MAX_LOGIN_ATTEMPTS)",
            bad: "if (attempts > 3)",
        );
        $rule->publish();
        $manager->persist($rule);

        // Documentation rules
        $rule = Rule::draft(
            DirectiveId::fromString('docs-rule-readme'),
            'README Requirements',
            'Every project needs a comprehensive README.',
        );
        $rule->updateContent('Include: description, installation, usage, contributing guidelines.');
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('docs-rule-inline-comments'),
            'Inline Comments',
            'Write comments that explain why, not what.',
        );
        $rule->updateContent('Code should be self-documenting. Comments explain intent and context.');
        Example::create(
            $rule,
            good: "// Offset by 1 because API uses 1-based indexing\nconst page = userPage - 1;",
            bad: "// subtract 1 from page\nconst page = userPage - 1;",
        );
        $manager->persist($rule);

        // Git/Version Control rules
        $rule = Rule::draft(
            DirectiveId::fromString('git-rule-branch-naming'),
            'Branch Naming Convention',
            'Use consistent branch naming patterns.',
        );
        $rule->updateContent('Format: type/issue-short-description (e.g., feat/dai-123-add-auth)');
        Example::create($rule, good: 'feat/dai-123-user-authentication', bad: 'my-branch');
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('git-rule-atomic-commits'),
            'Atomic Commits',
            'Each commit should represent a single logical change.',
        );
        $rule->updateContent('Don\'t mix unrelated changes in a single commit.');
        Example::create(
            $rule,
            good: "git commit -m 'feat: add login form'\ngit commit -m 'fix: correct validation'",
            bad: "git commit -m 'add login and fix stuff and update readme'",
        );
        $manager->persist($rule);

        // Performance rules
        $rule = Rule::draft(
            DirectiveId::fromString('perf-rule-lazy-loading'),
            'Lazy Loading',
            'Load resources only when needed.',
        );
        $rule->updateContent('Defer loading of non-critical resources to improve initial load time.');
        Example::create($rule, good: 'const Component = lazy(() => import("./HeavyComponent"));');
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('perf-rule-caching'),
            'Caching Strategy',
            'Implement appropriate caching for repeated operations.',
        );
        $rule->updateContent('Cache expensive computations, API responses, and database queries.');
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('perf-rule-n-plus-one'),
            'Avoid N+1 Queries',
            'Prevent N+1 query problems in database operations.',
        );
        $rule->updateContent('Use eager loading or batch queries instead of querying in loops.');
        Example::create(
            $rule,
            good: "users = User.query().with('posts').all()",
            bad: "for user in users:\n    user.posts  # triggers query each iteration",
            explanation: 'N+1 causes performance degradation that scales with data size.',
        );
        $rule->publish();
        $manager->persist($rule);

        // Accessibility rules
        $rule = Rule::draft(
            DirectiveId::fromString('a11y-rule-alt-text'),
            'Image Alt Text',
            'Provide meaningful alt text for images.',
        );
        $rule->updateContent('Alt text should describe the image content for screen readers.');
        Example::create(
            $rule,
            good: '<img src="chart.png" alt="Monthly revenue chart showing 20% growth">',
            bad: '<img src="chart.png" alt="image">',
        );
        $rule->publish();
        $manager->persist($rule);

        $rule = Rule::draft(
            DirectiveId::fromString('a11y-rule-semantic-html'),
            'Semantic HTML',
            'Use semantic HTML elements appropriately.',
        );
        $rule->updateContent('Use button, nav, main, article, etc. instead of generic divs.');
        Example::create($rule, good: '<button onClick={handleClick}>Submit</button>');
        Example::create($rule, bad: '<div onClick={handleClick}>Submit</div>');
        $manager->persist($rule);

        // Clear all domain events recorded during fixture creation
        DomainEventQueue::reset();

        $manager->flush();
    }
}
