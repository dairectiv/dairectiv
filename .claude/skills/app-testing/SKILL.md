---
name: app-testing
description: Guide for writing frontend tests including unit tests with Vitest, component tests, and visual/E2E tests with Playwright MCP. Use when creating tests for hooks, components, or pages.
allowed-tools: Read, Write, Edit, Glob, Grep, Bash, mcp__playwright__*
---

# Frontend Testing Guide

This Skill provides patterns for testing React applications using Vitest and Playwright MCP.

## When to Use

- Testing a new hook or component
- Writing visual tests with Storybook and Playwright
- Testing page behavior and user interactions
- Debugging UI issues with browser automation

## Testing Tools & URLs

| Tool       | Command                | URL                    |
|------------|------------------------|------------------------|
| Vitest     | `castor app:test`      | N/A (CLI)              |
| Storybook  | `castor app:storybook` | http://127.0.0.1:6006  |
| Dev Server | `castor app:dev`       | http://127.0.0.1:3000  |

## Test Types & When to Use Each

### 1. Unit Tests (Vitest) - Most Important

Test logic in isolation: hooks, utilities, state management.

**When to use:**
- Custom hooks with complex logic
- Utility functions
- State transformations
- Validation logic

**When NOT to use:**
- Simple UI components (use Storybook instead)
- Full page flows (use Playwright instead)

### 2. Component Tests (Vitest + React Testing Library)

Test component rendering and interactions.

**When to use:**
- Component with conditional rendering
- Component with user interactions
- Component with prop variations

### 3. Visual/E2E Tests (Playwright MCP)

Test the real UI in a browser.

**When to use:**
- Visual regression testing via Storybook
- Full user flow testing
- Debugging rendering issues
- Verifying responsive layouts

## Vitest Test Patterns

### Directory Structure

```
src/{bounded-context}/{entity}/{feature}/
├── __tests__/
│   ├── use-{feature}.test.ts     # Hook tests
│   ├── {feature}-list.test.tsx   # Component tests
│   └── {feature}.page.test.tsx   # Page tests
```

### Hook Tests

```typescript
// __tests__/use-feature.test.ts
import { renderHook, waitFor } from "@testing-library/react";
import { describe, expect, it, vi, beforeEach } from "vitest";
import { useFeature } from "../hooks/use-feature";

// Mock TanStack Router
vi.mock("@tanstack/react-router", () => ({
  useNavigate: () => vi.fn(),
  useSearch: () => ({ page: 1, search: "", state: undefined }),
}));

// Mock API queries
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listEntitiesOptions: () => ({
    queryKey: ["entities"],
    queryFn: async () => ({ items: mockItems, pagination: mockPagination }),
  }),
}));

const mockItems = [
  { id: "1", name: "Item 1", state: "draft" },
  { id: "2", name: "Item 2", state: "published" },
];

const mockPagination = { page: 1, limit: 10, total: 2, totalPages: 1 };

describe("useFeature", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should return items from API", async () => {
    const { result } = renderHook(() => useFeature());

    await waitFor(() => {
      expect(result.current.items).toEqual(mockItems);
    });
  });

  it("should return filters from URL search params", () => {
    const { result } = renderHook(() => useFeature());

    expect(result.current.filters).toEqual({
      page: 1,
      search: "",
      state: undefined,
    });
  });

  it("should call navigate when updating filters", () => {
    const mockNavigate = vi.fn();
    vi.mocked(useNavigate).mockReturnValue(mockNavigate);

    const { result } = renderHook(() => useFeature());
    result.current.setSearch("test");

    expect(mockNavigate).toHaveBeenCalledWith({
      to: expect.any(String),
      search: expect.objectContaining({ search: "test", page: 1 }),
    });
  });
});
```

### Component Tests

```typescript
// __tests__/feature-list.test.tsx
import { MantineProvider } from "@mantine/core";
import { render, screen, fireEvent } from "@testing-library/react";
import type { ReactNode } from "react";
import { describe, expect, it, vi } from "vitest";
import { FeatureList, type FeatureListProps } from "../components/feature-list";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

const defaultProps: FeatureListProps = {
  items: [],
  pagination: { page: 1, limit: 10, total: 0, totalPages: 1 },
  filters: {},
  isLoading: false,
  isError: false,
  error: null,
  onPageChange: vi.fn(),
  onSearchChange: vi.fn(),
  onStateChange: vi.fn(),
  onSortChange: vi.fn(),
};

const mockItems = [
  { id: "1", name: "Test Item", description: "Description", state: "draft" },
];

describe("FeatureList", () => {
  it("should render items", () => {
    renderWithProviders(<FeatureList {...defaultProps} items={mockItems} />);

    expect(screen.getByText("Test Item")).toBeInTheDocument();
    expect(screen.getByText("Description")).toBeInTheDocument();
  });

  it("should show loading state", () => {
    renderWithProviders(<FeatureList {...defaultProps} isLoading={true} />);

    const loader = document.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should show error state", () => {
    const error = new Error("Network failed");
    renderWithProviders(<FeatureList {...defaultProps} isError={true} error={error} />);

    expect(screen.getByText(/Network failed/)).toBeInTheDocument();
  });

  it("should show empty state when no items", () => {
    renderWithProviders(<FeatureList {...defaultProps} items={[]} />);

    expect(screen.getByText(/No.*found/i)).toBeInTheDocument();
  });

  it("should render search input", () => {
    renderWithProviders(<FeatureList {...defaultProps} />);

    expect(screen.getByPlaceholderText(/Search/i)).toBeInTheDocument();
  });

  it("should render pagination when multiple pages", () => {
    renderWithProviders(
      <FeatureList
        {...defaultProps}
        items={mockItems}
        pagination={{ page: 1, limit: 10, total: 50, totalPages: 5 }}
      />,
    );

    expect(screen.getByText(/Showing.*of.*50/)).toBeInTheDocument();
  });
});
```

### Test Commands

```bash
# Run all tests
castor app:test

# Run tests with coverage
castor app:test -c

# Run specific test file (use Vitest filter)
pnpm --dir app test -- --filter "use-feature"

# Run in watch mode (during development)
pnpm --dir app test -- --watch
```

## Playwright MCP Testing

Use Playwright MCP for visual testing and browser automation.

### Storybook Visual Testing

```typescript
// Navigate to Storybook
mcp__playwright__browser_navigate({ url: "http://127.0.0.1:6006" });

// Wait for Storybook to load
mcp__playwright__browser_wait_for({ text: "Storybook" });

// Take a snapshot of the page structure
mcp__playwright__browser_snapshot();

// Navigate to a specific story
mcp__playwright__browser_click({
  element: "Story link in sidebar",
  ref: "<ref from snapshot>"
});

// Take screenshot for visual verification
mcp__playwright__browser_take_screenshot({
  filename: "component-default.png"
});
```

### App E2E Testing

```typescript
// Navigate to app
mcp__playwright__browser_navigate({ url: "http://127.0.0.1:3000" });

// Wait for page to load
mcp__playwright__browser_wait_for({ text: "Expected content" });

// Capture page snapshot
mcp__playwright__browser_snapshot();

// Interact with elements
mcp__playwright__browser_type({
  element: "Search input",
  ref: "<ref from snapshot>",
  text: "search term"
});

// Click buttons
mcp__playwright__browser_click({
  element: "Submit button",
  ref: "<ref from snapshot>"
});

// Check console for errors
mcp__playwright__browser_console_messages({ level: "error" });

// Close browser when done
mcp__playwright__browser_close();
```

### Playwright MCP Tools Reference

| Tool                        | Purpose                                     |
|-----------------------------|---------------------------------------------|
| `browser_navigate`          | Go to a URL                                 |
| `browser_snapshot`          | Get accessibility tree (for element refs)  |
| `browser_take_screenshot`   | Capture visual state                        |
| `browser_click`             | Click an element                            |
| `browser_type`              | Type text into input                        |
| `browser_hover`             | Hover over element                          |
| `browser_wait_for`          | Wait for text/time                          |
| `browser_console_messages`  | Get console logs                            |
| `browser_network_requests`  | View network activity                       |
| `browser_close`             | Close the browser                           |

### Debugging with Playwright

When debugging UI issues:

1. **Take a snapshot first** to understand the page structure:
   ```typescript
   mcp__playwright__browser_snapshot();
   ```

2. **Check console for errors**:
   ```typescript
   mcp__playwright__browser_console_messages({ level: "error" });
   ```

3. **Check network requests**:
   ```typescript
   mcp__playwright__browser_network_requests();
   ```

4. **Take screenshot for visual evidence**:
   ```typescript
   mcp__playwright__browser_take_screenshot({ filename: "debug-issue.png" });
   ```

## Testing Best Practices

### What to Test

| Layer      | What to Test                           | How                           |
|------------|----------------------------------------|-------------------------------|
| Hooks      | Logic, state transformations           | Vitest + renderHook           |
| Components | Rendering, interactions, accessibility | Vitest + Testing Library      |
| Pages      | Composition, integration               | Vitest or Playwright          |
| Visual     | Appearance, responsiveness             | Storybook + Playwright        |

### Test Naming Convention

Use descriptive names that explain the behavior:

```typescript
// Good
it("should render items when data is loaded")
it("should show loading spinner while fetching")
it("should display error message on API failure")
it("should navigate to page 2 when clicking next")

// Bad
it("renders")
it("test loading")
it("error works")
```

### Mocking Patterns

**Mock Router:**
```typescript
vi.mock("@tanstack/react-router", () => ({
  useNavigate: () => vi.fn(),
  useSearch: () => ({ page: 1 }),
}));
```

**Mock API:**
```typescript
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listRulesOptions: () => ({
    queryKey: ["rules"],
    queryFn: async () => mockData,
  }),
}));
```

**Mock Zustand Store:**
```typescript
vi.mock("@shared/infrastructure/store", () => ({
  useAppStore: () => ({ theme: "light", setTheme: vi.fn() }),
}));
```

### Coverage Requirements

Run tests with coverage to identify gaps:

```bash
castor app:test -c
```

Focus coverage on:
- Custom hooks (business logic)
- Complex components (conditional rendering)
- Utility functions

Skip coverage for:
- Simple presentational components
- Generated API code
- Third-party integrations

## Checklist

When writing tests:
- [ ] Test happy path first
- [ ] Test error states
- [ ] Test loading states
- [ ] Test empty states
- [ ] Mock external dependencies
- [ ] Use descriptive test names
- [ ] Run `castor app:test` to verify all tests pass
- [ ] Run `castor app:lint -f` before committing

## Reference Files

- `app/src/authoring/rule/list/__tests__/` - Complete test examples
- `app/tests/` - Test utilities and mocks
- `app/vitest.config.ts` - Vitest configuration