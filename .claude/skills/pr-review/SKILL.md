---
name: pr-review
description: Handles PR review comments and applies requested fixes. Use when the user says "I commented on the PR", "check the PR comments", or mentions PR feedback to address.
allowed-tools: Bash, Read, Edit, Write, Glob, Grep
---

# PR Review Handler

This Skill helps process and address review comments from a GitHub Pull Request.

## When to Use

Use this Skill when the user:
- Says "I commented on the PR"
- Says "check the PR comments"
- Mentions there is feedback on the PR to address
- Asks to fix something from a PR review

## Workflow

### Step 1: Detect the Pull Request

First, find the PR associated with the current branch:

```bash
gh pr view --json number,url,title,comments,reviews
```

If no PR exists for the current branch, inform the user and ask for the PR number.

### Step 2: Fetch Comments

The `gh pr view` command returns both general comments and review comments:

**General comments** (via `--json comments`):
```json
{
  "comments": [
    {
      "id": "IC_kwDOQrsE3s7cCG53",
      "author": { "login": "TBoileau" },
      "body": "Please fix the typo in line 42",
      "createdAt": "2025-12-25T15:27:23Z",
      "url": "https://github.com/owner/repo/pull/10#issuecomment-123"
    }
  ]
}
```

**Review comments** (via `--json reviews`):
```json
{
  "reviews": [
    {
      "id": "PRR_kwDOQrsE3s6Q1234",
      "author": { "login": "TBoileau" },
      "body": "Overall looks good, but please address the comments below",
      "state": "CHANGES_REQUESTED",
      "submittedAt": "2025-12-25T15:30:00Z"
    }
  ]
}
```

**Inline review comments** (via `gh api`):
```bash
gh api repos/{owner}/{repo}/pulls/{pr_number}/comments
```

Returns comments attached to specific lines of code:
```json
[
  {
    "id": 123456,
    "path": "src/example.php",
    "line": 42,
    "body": "This should use sprintf instead",
    "user": { "login": "TBoileau" },
    "created_at": "2025-12-25T15:35:00Z"
  }
]
```

### Step 3: Analyze Comments

For each comment, determine:
1. **Type of feedback**:
   - **Fix**: Something is wrong and needs to be corrected
   - **Improvement**: A suggestion to make the code better
   - **Question**: Needs clarification or explanation
   - **Nitpick**: Minor style or formatting issue
   - **Praise**: Positive feedback (no action needed)

2. **Location**:
   - File and line number (for inline comments)
   - General (for PR-level comments)

3. **Priority**:
   - High: Bugs, security issues, breaking changes
   - Medium: Logic improvements, missing edge cases
   - Low: Style, naming, documentation

### Step 4: Filter Already Processed Comments

To avoid re-fixing issues:
1. Check if the comment references a specific line/file
2. Read the current state of that file
3. Determine if the issue has already been addressed
4. Skip comments that are already resolved

**Indicators that a comment is already addressed:**
- The line mentioned no longer exists
- The code at that location already matches what was requested
- A subsequent commit addresses the feedback

### Step 5: Apply Corrections

For each actionable comment:
1. Read the relevant file(s)
2. Understand the requested change
3. Apply the fix using Edit or Write tools
4. Verify the change is correct

### Step 6: Report Summary

After processing all comments, provide a summary:

```
## PR Review Summary

### Addressed
- [File:Line] Description of fix applied
- [File:Line] Description of fix applied

### Already Resolved
- [Comment] Reason why it was already fixed

### Needs Clarification
- [Comment] What needs to be clarified

### No Action Needed
- [Comment] Praise or informational only
```

## Best Practices

1. **Read before editing**: Always read the file context before making changes
2. **One fix at a time**: Apply fixes incrementally, not all at once
3. **Preserve intent**: Don't over-engineer or add unrelated changes
4. **Verify fixes**: After applying a fix, re-read to confirm it's correct
5. **Ask when unclear**: If a comment is ambiguous, ask for clarification
6. **Respect code style**: Match the existing code style in the project

## Edge Cases

### No PR Found
```
No pull request found for the current branch 'feature-xyz'.
Would you like me to check a specific PR number?
```

### No Comments
```
The PR has no review comments to process.
```

### Comment Already Addressed
```
Comment by @TBoileau on src/example.php:42:
"Use sprintf instead of string interpolation"

Status: Already addressed - the code now uses sprintf.
```

### Unclear Comment
```
Comment by @TBoileau:
"This needs to be fixed"

This comment is unclear. Could you clarify what specifically needs to be fixed?
```

## Notes

- This Skill does NOT create Linear issues unless explicitly requested
- Focus on code fixes, not workflow changes
- If a comment suggests a large refactor, discuss with the user before proceeding
- Keep commits atomic - one fix per commit if committing