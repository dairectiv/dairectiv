---
name: linear-issue
description: Creates structured Linear issues following team conventions. Use when the user asks to create a ticket, issue, or task in Linear, or mentions tracking work, bugs, features, improvements, chores, or spikes.
allowed-tools: AskUserQuestion, mcp__linear__create_issue, mcp__linear__update_issue, mcp__linear__get_issue, mcp__linear__list_teams, mcp__linear__list_projects, mcp__linear__list_issue_labels, mcp__linear__get_team
---

# Linear Issue Creator

This Skill helps create well-structured Linear issues following the team's conventions and templates.

## When to Use

Use this Skill when the user:
- Asks to create a Linear ticket/issue/task
- Reports a bug that should be tracked
- Suggests a feature or improvement
- Mentions creating a spike or investigation task
- Asks to track technical debt or chores
- Asks to refine an existing issue

## Default Values

Every issue created or refined via this Skill MUST have:
- **Assignee**: "Thomas Boileau"
- **Project**: "dairectiv"
- **Status**: "Todo" (refined issues go to Todo, unrefined stay in Backlog)
- **Estimation**: Required (see Estimation Guide below)

## Estimation Guide

Use the Fibonacci scale for estimation. Estimation reflects complexity and effort:

| Points  | Description                                                                   | Examples                                            |
|---------|-------------------------------------------------------------------------------|-----------------------------------------------------|
| **1**   | Straightforward, no complexity. Can be done very quickly manually or with AI. | Skills, Subagents, CLAUDE.md updates, trivial fixes |
| **2**   | Light complexity manually or moderate with AI.                                | Simple feature, small refactor                      |
| **3**   | Moderate complexity manually, or high with AI.                                | Feature with multiple files, integration work       |
| **5**   | High complexity, often introduces new technique. **Default for Spike**.       | New architectural pattern, complex feature          |
| **8**   | Very large technical undertaking, even with AI.                               | Major refactoring, new bounded context              |

### Estimation by Issue Type

- **Spike**: Default to **5** (investigation effort is unpredictable)
- **Documentation**: **1-3** max (1 for Skills/Subagents/CLAUDE.md, higher for extensive docs)
- **Bugfix**: Same rules as other types (can range from 1 to 8 depending on complexity)
- **Chore/CI/Build**: Same rules as other types

## Refined vs Unrefined Issues

| State         | Status  | Description                                                                         |
|---------------|---------|-------------------------------------------------------------------------------------|
| **Refined**   | Todo    | Issue created/updated via this Skill with full template, estimation, and assignment |
| **Unrefined** | Backlog | Issue created quickly without proper structure (to be refined later)                |

### Refinement Workflow

When asked to refine an existing issue:
1. Fetch the issue using `mcp__linear__get_issue`
2. Update the description with the appropriate template
3. Add/adjust the estimation
4. Change status from Backlog → Todo
5. Ensure assignee is "Thomas Boileau" and project is "dairectiv"

## Workflow

### Step 1: Identify Issue Type

Determine the appropriate issue type based on the request:
- **Feature**: New functionality or capability
- **Bugfix**: Something is broken and needs fixing
- **Refactor**: Code refactoring without behavior change
- **Performance**: Performance improvements
- **Documentation**: Adding or updating documentation
- **Test**: Adding or updating tests
- **Chore**: Maintenance, dependencies, or tooling
- **Style**: UI/styling changes
- **Build**: Build system or dependencies
- **CI**: CI/CD configuration
- **Revert**: Reverting previous changes
- **Spike**: Research or investigation needed

If unclear, ask the user which type fits best.

### Step 2: Gather Information

Based on the issue type, collect the necessary information from the conversation or ask the user to clarify missing details.

### Step 3: Determine Estimation

Based on the issue type and complexity:
1. Assess the technical complexity
2. Consider if new techniques are introduced
3. Apply the Fibonacci scale (1, 2, 3, 5, 8)
4. Use defaults for specific types (Spike = 5, Documentation = 1-3)

### Step 4: Fill Template

Use the appropriate template below and fill it with the gathered information. Keep it concise - provide only what's needed to understand, plan, and validate.

### Step 5: Create or Update Issue via MCP

Use the Linear MCP to create/update the issue with:
- **Title**: Short, action-oriented (e.g., "Add user authentication", "Fix database connection timeout")
- **Description**: The filled template (Markdown format)
- **Project**: `dairectiv` (always)
- **Assignee**: "Thomas Boileau" (always)
- **Label**: Map the issue type to the correct label (see Label Mapping below)
- **Estimate**: The determined estimation (1, 2, 3, 5, or 8)
- **Status**: "Todo" (refined issues)
- **Write in English**: All issue content must be in English

### Step 6: Sanity Check

Before creating/updating, verify the issue includes:
- Clear goal or problem statement
- Concrete success criteria or acceptance
- Estimation based on complexity
- Any dependencies or risks if applicable
- Issue status should be "Todo"
- Assignee should be "Thomas Boileau"

## Label Mapping

Map each issue type 1-to-1 with its Linear label:

- Feature → `Feature`
- Bugfix → `Bugfix`
- Refactor → `Refactor`
- Performance → `Performance`
- Documentation → `Documentation`
- Test → `Test`
- Chore → `Chore`
- Style → `Style`
- Build → `Build`
- CI → `CI`
- Revert → `Revert`
- Spike → `Spike`

## Issue Templates

### Feature Template

```markdown
# ✨ Feature

## 🧠 Context
- [Why this is needed - 1-2 bullets]

## 🎯 Goal
- [What outcome we want]

## ✅ Success Criteria
- [Measurable outcome 1]
- [Measurable outcome 2]
- [Measurable outcome 3]

## 🔗 Dependencies (Optional)
- [External services, teams, or blockers if any]
```

### Bugfix Template

```markdown
# 🐛 Bugfix

## ❗ Issue
- [What is broken and where]

## 🔎 Investigation
- [Key findings or suspected root cause]

## 🧭 Plan
- [High-level fix step 1]
- [High-level fix step 2]
- [High-level fix step 3]

## ✅ Acceptance
- [How we know it is fixed]
```

### Improvement Template

```markdown
# 🛠 Improvement

## 🧠 Context
- [What could be improved and why]

## 🎯 Goal
- [Target outcome or desired change]

## ✅ Success Criteria
- [Observable result 1]
- [Observable result 2]
- [Observable result 3]
```

### Chore Template

```markdown
# 🧹 Chore

## 🧩 Task
- [What needs to be done]

## 🧭 Plan
- [Step 1]
- [Step 2]
- [Step 3]

## ✅ Done When
- [Clear completion condition 1]
- [Clear completion condition 2]
```

### Spike Template

```markdown
# 🧪 Spike

## ❓ Question
- [What we need to learn]

## 🔍 Investigation
- [Approach or sources to explore]

## 📦 Output
- [Expected deliverable: doc, summary, or decision]
```

### Documentation Template

```markdown
# 📚 Documentation

## 🧩 Task
- [What documentation needs to be added or updated]

## 🎯 Goal
- [Target audience and purpose]

## ✅ Done When
- [Documentation is complete and reviewed]
```

### Refactor Template

```markdown
# ♻️ Refactor

## 🧠 Context
- [What code needs refactoring and why]

## 🎯 Goal
- [Desired code structure or organization]

## 🧭 Plan
- [Step 1: what to extract/rename/reorganize]
- [Step 2]
- [Step 3]

## ✅ Done When
- [Code is cleaner/more maintainable]
- [All tests still pass]
- [No behavior changes]
```

### Performance Template

```markdown
# ⚡ Performance

## 🐌 Problem
- [What is slow and where]
- [Current metrics/measurements]

## 🎯 Goal
- [Target performance improvement]

## 🧭 Plan
- [Optimization approach]
- [Areas to focus on]

## ✅ Success Criteria
- [Measurable performance metrics]
- [Before/after comparison]
```

### Test Template

```markdown
# ✅ Test

## 🧩 Task
- [What needs test coverage]

## 🧭 Plan
- [Test scenarios to cover]
- [Edge cases to handle]

## ✅ Done When
- [Test coverage is adequate]
- [All tests pass]
```

### Style Template

```markdown
# 💄 Style

## 🧩 Task
- [UI/styling changes needed]

## 🎯 Goal
- [Desired visual outcome]

## ✅ Done When
- [Visual changes are complete]
- [Responsive design works]
- [Design is reviewed]
```

### Build Template

```markdown
# 🏗️ Build

## 🧩 Task
- [Build system or dependency changes needed]

## 🧭 Plan
- [What to update/configure]
- [Impact assessment]

## ✅ Done When
- [Build works correctly]
- [Dependencies are updated]
- [CI passes]
```

### CI Template

```markdown
# 👷 CI

## 🧩 Task
- [CI/CD configuration changes needed]

## 🧭 Plan
- [Pipeline/workflow modifications]
- [New steps or jobs]

## ✅ Done When
- [CI configuration is working]
- [All checks pass]
```

### Revert Template

```markdown
# ⏪ Revert

## ❗ Problem
- [What needs to be reverted and why]
- [Impact of the original change]

## 🧭 Plan
- [What commits/changes to revert]
- [Clean-up steps]

## ✅ Done When
- [Revert is complete]
- [System is stable]
```

## Best Practices

1. **Keep it concise**: Only include what's needed for understanding and action
2. **Be specific**: Use concrete examples and measurable criteria
3. **Use English**: All issue content must be in English
4. **Action-oriented titles**: Start with a verb (Add, Fix, Improve, Refactor, etc.)
5. **Include context**: Help future readers understand why this matters
6. **Define success**: Make it clear how to verify the work is complete
7. **Always estimate**: Every refined issue must have an estimation

## Examples

### Good Title Examples
- ✓ "Add PostgreSQL connection pooling"
- ✓ "Fix memory leak in API worker process"
- ✓ "Improve database migration performance"
- ✓ "Refactor Castor task organization"
- ✓ "Research OAuth 2.0 providers for authentication"

### Bad Title Examples
- ✗ "Database stuff" (too vague)
- ✗ "Bug" (no context)
- ✗ "Things to do" (not specific)
- ✗ "Investigate" (missing what to investigate)
- ✗ "[Rules] Display rules list" (no bracketed prefixes - use plain title instead)
- ✗ "[API] Add endpoint" (prefixes add noise, issue labels/project already provide context)

## Notes

- If the user's request is unclear, ask clarifying questions before creating the issue
- If multiple issues are needed, create them one at a time
- After creating an issue, provide the user with the Linear issue URL
- If the user wants to add more details later, they can edit the issue in Linear directly
- When refining an existing issue, always update status to "Todo"
