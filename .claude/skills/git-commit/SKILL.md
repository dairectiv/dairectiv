---
name: git-commit
description: Creates structured Git commit messages following Conventional Commits format. Use when the user asks to commit changes, create a commit, or mentions committing staged changes.
allowed-tools: Bash, AskUserQuestion, mcp__linear__get_issue, mcp__linear__update_issue
---

# Git Commit Message Generator

This Skill helps create well-structured Git commit messages following the team's Conventional Commits conventions, synchronized with Linear issue types.

## When to Use

Use this Skill when the user:
- Asks to commit changes
- Says "create a commit" or "commit these changes"
- Mentions staging and committing work
- Asks for a commit message suggestion

## Workflow

### Step 1: Gather Git Context

Execute these Git commands to understand the changes:

```bash
# Current git status
git status

# Staged and unstaged changes
git diff HEAD

# Current branch name
git branch --show-current

# Recent commit history (for style consistency)
git log --oneline -10
```

### Step 2: Identify Commit Type(s)

**Analyze the changes and determine if they belong to one or multiple types.**

| Type         | Description                               | Linear Label  |
|--------------|-------------------------------------------|---------------|
| `feat`       | New features or capabilities              | Feature       |
| `fix`        | Bug fixes                                 | Bugfix        |
| `refactor`   | Code refactoring without behavior change  | Refactor      |
| `perf`       | Performance improvements                  | Performance   |
| `docs`       | Documentation changes                     | Documentation |
| `test`       | Adding or updating tests                  | Test          |
| `chore`      | Maintenance, dependencies, tooling        | Chore         |
| `style`      | UI/styling changes                        | Style         |
| `build`      | Build system or dependencies              | Build         |
| `ci`         | CI/CD configuration                       | CI            |
| `revert`     | Reverting previous changes                | Revert        |
| `spike`      | Research or experimentation               | Spike         |

**Important**: If changes span multiple types (e.g., feature + docs + tests), **do not create a single mixed commit**. Instead, proceed to Step 2a to split into atomic commits.

### Step 2a: Handle Mixed Changes (Multiple Types)

**If changes include multiple types, create separate atomic commits:**

1. **Identify all change types** present in the diff:
   - Feature code
   - Bug fixes
   - Documentation updates
   - Test additions
   - Configuration changes
   - etc.

2. **Group files by type**:
   ```bash
   # Example: Changes include feature + tests + docs
   Feature files:
   - src/Controller/AuthController.php
   - src/Service/AuthService.php

   Test files:
   - tests/Controller/AuthControllerTest.php

   Documentation files:
   - CLAUDE.md
   - README.md
   ```

3. **Propose a commit strategy** to the user:
   - Commit 1 (feat): Feature implementation files
   - Commit 2 (test): Test files
   - Commit 3 (docs): Documentation updates

4. **Guide the user** through staging and committing each group:
   ```bash
   # Commit 1: Feature
   git add src/Controller/AuthController.php src/Service/AuthService.php
   git commit -m "feat(dai-16): add user authentication with OAuth2"

   # Commit 2: Tests
   git add tests/Controller/AuthControllerTest.php
   git commit -m "test(dai-16): add tests for authentication controller"

   # Commit 3: Documentation
   git add CLAUDE.md README.md
   git commit -m "docs(dai-16): document OAuth2 authentication setup"
   ```

5. **Ask user confirmation** before executing each commit.

**When to use multiple commits:**
- ✅ Feature code + tests → 2 commits (feat + test)
- ✅ Feature + documentation → 2 commits (feat + docs)
- ✅ Refactoring + performance + tests → 3 commits
- ✅ Bug fix + related test updates → 2 commits (fix + test)

**When a single commit is acceptable:**
- ✅ Only feature code (cohesive change)
- ✅ Only bug fix (single logical fix)
- ✅ Only documentation updates
- ✅ Only configuration changes

### Step 3: Extract Branch Context & Fetch Linear Issue

**Extract issue reference from branch name:**

If the current branch follows the pattern `dai-XXX`, extract the issue number.

Examples:
- Branch: `dai-16` → Extract issue identifier `DAI-16`
- Branch: `main`, `develop`, `feature/something` → No issue reference

**Fetch Linear issue details (when branch matches `dai-XXX`):**

1. Use the Linear MCP to fetch the issue:
   ```
   mcp__linear__get_issue(id: "DAI-16")
   ```

2. Extract useful information from the issue:
   - **Title**: Use as inspiration for commit message title
   - **Labels**: Verify commit type matches issue label
   - **Description**: Understand context for better commit message

3. **Validate type consistency**:
   - If issue has label "Feature" but changes are tests → Type should be `test`, not `feat`
   - If issue has label "Bugfix" but changes are docs → Type should be `docs`, not `fix`
   - The commit type reflects the **current changes**, not the overall issue type

**Example workflow:**

```bash
# Current branch: dai-16
# Fetch issue: DAI-16 "Add user authentication with OAuth2"
# Issue label: Feature

# Current changes analysis:
# - Modified: src/Controller/AuthController.php
# - Type: feat ✓ (matches issue label)

# Commit message:
feat(dai-16): add user authentication with OAuth2
```

**Note**: If Linear API is unavailable or issue not found, continue with just the issue reference (dai-16) without additional context.

### Step 4: Generate Commit Message

Follow this format:

```
<type>[(<issue-ref>)]: <title>

<optional body>
```

**Structure Rules:**
- **Type**: Required, lowercase, from the table above
- **Issue reference**: Optional, format `dai-XXX`, only if branch name contains it
- **Title**: Short description (max 72 chars), lowercase, no period at end
- **Body**: Optional, provide context only if changes are complex or need explanation

**Examples:**
```
feat(dai-16): add user authentication with OAuth2
```

```
fix(dai-23): resolve N+1 query in workflow submission generator
```

```
chore: upgrade symfony to 8.0 and doctrine to 3.5
```

```
refactor(dai-45): extract castor tasks into separate files

Reorganized castor tasks for better maintainability:
- docker.php: infrastructure commands
- database.php: database operations
- quality.php: QA tools
```

### Step 5: Review & Confirm

Before committing:
1. Verify the type matches the changes
2. Ensure the title is clear and concise
3. Check if body is needed (complex changes only)
4. Confirm with user if uncertain

### Step 6: Run Quality Checks

**Before staging and committing, always run QA with auto-fix:**

```bash
castor qa -f
```

This will:
- Run Rector with auto-fix
- Run ECS (code style) with auto-fix
- Run linters
- Validate Doctrine schema
- Run PHPStan
- Run PHPUnit tests

**Wait for QA to pass before proceeding.** If tests fail, fix the issues first.

### Step 7: Create Commit

After QA passes and user approval, execute:
```bash
git add -A
git commit -m "<commit message>"
```

If body is needed:
```bash
git add -A
git commit -m "<title>" -m "<body>"
```

### Step 8: Update Linear Issue Description (if empty)

**After successful commit, check if the Linear issue has an empty description:**

1. If the issue description was empty (or minimal) when fetched in Step 3:
   - Generate a description based on the committed changes
   - Use the mcp__linear__update_issue tool to update the description

2. **Description format:**
```markdown
# <Type Emoji> <Type Name>

## Context
- [Brief context about why this was needed]

## Changes
- [List of main changes made]

## Done When
- [Completion criteria based on what was implemented]
```

3. **Type emoji mapping:**
   - Feature: ✨
   - Bugfix: 🐛
   - Refactor: ♻️
   - Performance: ⚡
   - Documentation: 📚
   - Test: ✅
   - Chore: 🧹
   - Spike: 🧪

4. **Example:**
```markdown
# ✨ Feature

## Context
- Need to retrieve a single rule by its ID for display purposes

## Changes
- Create Get Rule Query with Input, Output, and Handler
- Handler uses RuleRepository to fetch rule by ID
- Add integration tests for success and not found scenarios

## Done When
- Query returns rule with all properties (name, description, content, examples)
- Returns RuleNotFoundException when rule doesn't exist
- All tests pass
```

**Note**: Only update if description is empty or contains just a placeholder. Don't overwrite detailed descriptions that already exist.

## Important Notes

### What NOT to Include

❌ **DO NOT** include Claude Code signatures in commit messages:
```
🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

❌ **DO NOT** use verbose or unnecessary details
❌ **DO NOT** describe HOW the change was made (code details), describe WHAT and WHY

### Best Practices

✅ Use present tense: "add feature" not "added feature"
✅ Keep title under 72 characters
✅ Use lowercase for title
✅ Be specific: mention component/area affected
✅ Use body only when context is needed
✅ Follow the team's existing commit style (check recent commits)

## Type Selection Guidelines

**feat** - New functionality
- Adding a new API endpoint
- Implementing a new user feature
- Adding a new Castor command

**fix** - Fixing broken functionality
- Resolving a bug
- Fixing a crash or error
- Correcting wrong behavior

**refactor** - Restructuring code
- Extracting methods/classes
- Renaming for clarity
- Reorganizing file structure

**perf** - Performance optimization
- Optimizing queries
- Reducing memory usage
- Improving response times

**docs** - Documentation only
- README updates
- Code comments
- CLAUDE.md changes

**test** - Test changes only
- Adding tests
- Fixing test failures
- Updating test data

**chore** - Maintenance work
- Dependency updates
- Configuration changes
- Build script updates

**spike** - Research/experimentation
- Proof of concepts
- Investigation work
- Exploring new libraries

## Correlation with Linear Issues

The commit types are synchronized 1-to-1 with Linear issue labels:

| Commit Type | Linear Label  | Description                          |
|-------------|---------------|--------------------------------------|
| `feat`      | Feature       | New features or capabilities         |
| `fix`       | Bugfix        | Bug fixes                            |
| `refactor`  | Refactor      | Code refactoring                     |
| `perf`      | Performance   | Performance improvements             |
| `docs`      | Documentation | Documentation changes                |
| `test`      | Test          | Adding or updating tests             |
| `chore`     | Chore         | Maintenance, dependencies, tooling   |
| `style`     | Style         | UI/styling changes                   |
| `build`     | Build         | Build system or dependencies         |
| `ci`        | CI            | CI/CD configuration                  |
| `revert`    | Revert        | Reverting previous changes           |
| `spike`     | Spike         | Research or experimentation          |

When working on a Linear issue, always include the issue reference in the commit message if the branch follows the `dai-XXX` pattern.

## Examples by Scenario

### Feature Implementation
```
feat(dai-16): add PostgreSQL connection pooling

Implemented connection pooling to improve database performance:
- Added pool configuration in doctrine.yaml
- Set max connections to 20
- Added monitoring for pool usage
```

### Bug Fix
```
fix(dai-23): resolve memory leak in API worker process
```

### Refactoring
```
refactor: extract castor tasks into organized files
```

### Documentation
```
docs: update CLAUDE.md with git commit conventions
```

### Maintenance
```
chore: upgrade symfony dependencies to latest patch versions
```

### Performance
```
perf(dai-34): optimize doctrine queries to reduce N+1 problems
```

### Mixed Changes (Multiple Commits)

**Scenario**: You've implemented authentication (feature), added tests, and updated documentation.

**Wrong approach (single mixed commit):**
```
❌ feat(dai-16): add authentication, tests, and docs
```

**Correct approach (3 atomic commits):**
```
✅ Commit 1:
feat(dai-16): add user authentication with OAuth2

✅ Commit 2:
test(dai-16): add tests for authentication controller

✅ Commit 3:
docs(dai-16): document OAuth2 authentication setup
```

**How to execute:**
```bash
# Stage and commit feature files only
git add src/Controller/AuthController.php src/Service/AuthService.php
git commit -m "feat(dai-16): add user authentication with OAuth2"

# Stage and commit test files only
git add tests/Controller/AuthControllerTest.php
git commit -m "test(dai-16): add tests for authentication controller"

# Stage and commit documentation only
git add CLAUDE.md README.md
git commit -m "docs(dai-16): document OAuth2 authentication setup"
```

## Integration with git-flow & Linear

**Branch Naming Convention:**

Each Linear issue corresponds to exactly one git branch following the pattern `dai-XXX`:

- Linear automatically suggests the branch name in the issue's `gitBranchName` field
- Example: Issue `DAI-16` → Branch name `dai-16`
- Always use lowercase for branch names

**Workflow:**
1. Create/assign Linear issue
2. Create branch: `git checkout -b dai-16`
3. Work on the issue
4. Commit with Skill assistance (auto-fetches Linear context)
5. Create PR when ready

**Special branches:**
- `main`, `develop`: No issue reference in commits
- Infrastructure/tooling branches: No issue reference

## Notes

- Always check recent commit history to match the team's style
- If unsure about the type, ask the user
- **CRITICAL**: For changes spanning multiple types, **always split into separate commits**
  - Never mix feat + test + docs in one commit
  - Each commit should have a single, clear type
  - Use `git add <specific-files>` to stage only related files
- Keep commits atomic - one logical change per commit
- Atomic commits make code review easier and rollbacks safer
- It's better to have 3 small focused commits than 1 large mixed commit
