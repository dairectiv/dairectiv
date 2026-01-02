---
name: frontend-imports
description: Guide for organizing imports using barrel exports in frontend features. Use when creating or refactoring feature modules.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Frontend Import Patterns

This Skill provides patterns for organizing imports using barrel exports in the frontend codebase.

## When to Use

- Creating a new frontend feature module
- Refactoring imports in existing features
- Organizing exports in a feature's index.ts

## Barrel Export Pattern

Each feature module has a single `index.ts` that exports all public APIs. Internal files import from this barrel export.

### Feature Index Structure

```typescript
// src/{bounded-context}/{entity}/{feature}/index.ts

// Components
export { ComponentA, type ComponentAProps } from "./components/component-a";
export { ComponentB } from "./components/component-b";

// Hooks
export { useFeatureHook, type HookReturnType } from "./hooks/use-feature-hook";

// Pages
export { FeaturePage } from "./pages/feature.page";

// Routes
export { featureRoute, type FeatureSearch } from "./routes/feature.route";
```

## Import Rules

### 1. Cross-Feature Imports

Always use the barrel export path alias:

```typescript
// Good - use barrel export
import { RulesList, useRulesList, type RulesListStateFilter } from "@/authoring/rule/list";

// Bad - direct file path
import { RulesList } from "@/authoring/rule/list/components/rules-list";
import { useRulesList } from "@/authoring/rule/list/hooks/use-rules-list";
```

### 2. Same-Feature Imports (CRITICAL)

**Use relative imports** for imports within the same feature to avoid circular dependencies:

```typescript
// components/rules-list.tsx

// Good - relative imports within same feature
import type { RulesListStateFilter } from "../hooks/use-rules-list";
import { RulesListEmpty } from "./rules-list-empty";
import { RulesListToolbar } from "./rules-list-toolbar";

// Bad - barrel export causes circular dependency
import { RulesListEmpty, RulesListToolbar, type RulesListStateFilter } from "@/authoring/rule/list";
```

**Why?** The barrel export (`index.ts`) includes routes, and routes import the router which triggers initialization. This causes test failures and potential runtime issues.

**Rule of thumb:**
- **Inside a feature**: Use relative imports (`./`, `../`)
- **Outside a feature**: Use barrel export (`@/authoring/rule/list`)

### 3. External Library Imports

Keep external imports separate and at the top:

```typescript
// External libraries first
import { Center, Group, Loader } from "@mantine/core";
import { useQuery } from "@tanstack/react-query";

// Then internal imports via barrel
import { RulesListToolbar, type RulesListStateFilter } from "@/authoring/rule/list";
```

## Path Aliases

Use path aliases instead of relative paths:

| Alias | Path |
|-------|------|
| `@/*` | `src/*` |
| `@shared/*` | `src/shared-kernel/*` |

```typescript
// Good
import { AppLayout } from "@shared/ui/layout";
import { RulesList } from "@/authoring/rule/list";

// Bad
import { AppLayout } from "../../../shared-kernel/ui/layout";
import { RulesList } from "../components/rules-list";
```

## Export Types

Always export types alongside their implementations:

```typescript
// index.ts
export { RulesList, type RulesListProps } from "./components/rules-list";
export { useRulesList, type RulesListStateFilter, type RulesListFilters } from "./hooks/use-rules-list";
```

## Biome Import Ordering

Biome automatically sorts imports. The order is:

1. Type imports first (alphabetically)
2. Value imports second (alphabetically)

```typescript
// Biome will format to:
import { type RulesListStateFilter, RulesListEmpty, RulesListToolbar } from "@/authoring/rule/list";
```

## Checklist

When creating a feature module:
- [ ] Create `index.ts` in feature root
- [ ] Export all public components, hooks, pages, routes
- [ ] Export all public types with `type` keyword
- [ ] Use **relative imports** within the same feature (avoid circular deps)
- [ ] Use **barrel exports** for cross-feature imports
- [ ] Use path aliases (`@/`, `@shared/`) for external features
- [ ] Run `castor app:lint -f` to fix import ordering

## Example: Rules List Feature

```
src/authoring/rule/list/
├── __tests__/
│   ├── rules-list.test.tsx       # imports directly from ../components/
│   └── use-rules-list.test.ts    # mocks modules to avoid router init
├── components/
│   ├── rules-list.tsx            # uses relative imports: ./rules-list-empty
│   ├── rules-list-toolbar.tsx    # uses relative imports
│   └── rules-list-empty.tsx
├── hooks/
│   └── use-rules-list.ts
├── pages/
│   └── rules-list.page.tsx       # can use barrel (it's the entry point)
├── routes/
│   └── rules-list.route.ts
└── index.ts                      # exports everything for external consumers
```

## Circular Dependency Troubleshooting

**Symptom**: Tests fail with `Cannot read properties of undefined (reading 'init')` from router.

**Cause**: A component imports from the barrel export which includes routes. Routes import the router, triggering initialization before tests can mock it.

**Solution**:
1. Use relative imports within features
2. In tests, import directly from component files:
   ```typescript
   // In __tests__/rules-list.test.tsx
   import { RulesList } from "../components/rules-list";
   ```

## Reference Files

- `app/src/authoring/rule/list/index.ts` - Feature barrel export
- `app/src/authoring/rule/list/components/rules-list.tsx` - Uses relative imports