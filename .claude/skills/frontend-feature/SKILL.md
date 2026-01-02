---
name: frontend-feature
description: Guide for implementing frontend features following the feature-first architecture. Use when creating new pages, lists, forms, or CRUD features.
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Frontend Feature Implementation Guide

This Skill provides patterns for implementing frontend features in the dairectiv application.

## When to Use

- Creating a new page or feature
- Implementing a list with pagination, filtering, sorting
- Creating forms with validation
- Adding CRUD operations

## Architecture Overview

Features are organized by bounded context and entity, using a feature-first structure with subdirectories.

### Directory Structure

```
src/{bounded-context}/{entity}/{feature}/
├── components/           # Presentational components
│   ├── {feature}-list.tsx
│   ├── {feature}-toolbar.tsx
│   └── {feature}-empty.tsx
├── hooks/               # Data fetching and state logic
│   └── use-{feature}.ts
├── pages/               # Page components (composition layer)
│   └── {feature}.page.tsx
├── routes/              # Route definitions with search params
│   └── {feature}.route.ts
├── __tests__/           # Tests for all layers
│   ├── use-{feature}.test.ts
│   ├── {feature}-list.test.tsx
│   └── {feature}.page.test.tsx
└── index.ts             # Barrel export
```

### Dependency Flow

```
routes → pages → components → hooks
         ↓
      index.ts (barrel export)
```

## Step-by-Step Implementation

### Step 1: Create Route Definition

The route defines the URL path, search parameters, and connects to the page component.

```typescript
// routes/{feature}.route.ts
import { createRoute } from "@tanstack/react-router";
import { z } from "zod";
import { rootRoute } from "@/router";
import { FeaturePage } from "@/{context}/{entity}/{feature}";

const searchSchema = z.object({
  page: z.number().min(1).optional().default(1),
  search: z.string().optional(),
  state: z.enum(["draft", "published", "archived"]).optional(),
  sortBy: z.enum(["name", "createdAt", "updatedAt"]).optional(),
  sortOrder: z.enum(["asc", "desc"]).optional(),
});

export type FeatureSearch = z.infer<typeof searchSchema>;

export const featureRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/{context}/{entity}",
  component: FeaturePage,
  validateSearch: searchSchema,
});
```

### Step 2: Register Route in Router

Add the route to `src/router.tsx`:

```typescript
import { featureRoute } from "@/{context}/{entity}/{feature}";

const routeTree = rootRoute.addChildren([
  indexRoute,
  featureRoute,  // Add new route
]);
```

### Step 3: Create Hook

The hook manages data fetching, URL state, and filter/sort logic.

```typescript
// hooks/use-{feature}.ts
import { listEntitiesOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { useQuery } from "@tanstack/react-query";
import { useNavigate, useSearch } from "@tanstack/react-router";

export type StateFilter = "draft" | "published" | "archived";

export interface Filters {
  page: number;
  search?: string;
  state?: StateFilter;
  sortBy?: "name" | "createdAt" | "updatedAt";
  sortOrder?: "asc" | "desc";
}

export function useFeature() {
  const navigate = useNavigate();
  const filters = useSearch({ from: "/context/entity" }) as Filters;

  const { data, isLoading, isError, error } = useQuery(
    listEntitiesOptions({
      query: {
        page: filters.page,
        limit: 10,
        search: filters.search,
        state: filters.state,
        sortBy: filters.sortBy,
        sortOrder: filters.sortOrder,
      },
    }),
  );

  const updateFilters = (newFilters: Partial<Filters>) => {
    navigate({
      to: "/context/entity",
      search: { ...filters, ...newFilters },
    });
  };

  const setPage = (page: number) => updateFilters({ page });
  const setSearch = (search: string) => updateFilters({ search: search || undefined, page: 1 });
  const setState = (state: StateFilter | undefined) => updateFilters({ state, page: 1 });
  const setSort = (sortBy: Filters["sortBy"], sortOrder: Filters["sortOrder"]) =>
    updateFilters({ sortBy, sortOrder });

  return {
    items: data?.items ?? [],
    pagination: data?.pagination,
    filters,
    isLoading,
    isError,
    error,
    setPage,
    setSearch,
    setState,
    setSort,
  };
}
```

### Step 4: Create Components

#### List Component (Presentation)

```typescript
// components/{feature}-list.tsx
import { Center, Group, Loader, Pagination, Stack, Text } from "@mantine/core";
import type { PaginationResponse, EntityResponse } from "@shared/infrastructure/api/generated/types.gen";
import { ListCard, StateBadge } from "@shared/ui/data-display";
import { FeatureEmpty, FeatureToolbar, type StateFilter } from "@/{context}/{entity}/{feature}";

export interface FeatureListProps {
  items: EntityResponse[];
  pagination?: PaginationResponse;
  filters: {
    search?: string;
    state?: StateFilter;
    sortBy?: "name" | "createdAt" | "updatedAt";
    sortOrder?: "asc" | "desc";
  };
  isLoading: boolean;
  isError: boolean;
  error?: Error | null;
  onPageChange: (page: number) => void;
  onSearchChange: (search: string) => void;
  onStateChange: (state: StateFilter | undefined) => void;
  onSortChange: (sortBy: "name" | "createdAt" | "updatedAt", sortOrder: "asc" | "desc") => void;
}

export function FeatureList({ items, pagination, filters, isLoading, isError, error, ...handlers }: FeatureListProps) {
  return (
    <Stack gap="md">
      <FeatureToolbar {...filters} {...handlers} />

      {isLoading && <Center py="xl"><Loader size="lg" /></Center>}

      {isError && (
        <Center py="xl">
          <Text c="red">{error?.message ?? "An error occurred"}</Text>
        </Center>
      )}

      {!isLoading && !isError && items.length === 0 && <FeatureEmpty />}

      {!isLoading && !isError && items.length > 0 && (
        <>
          <Stack gap="xs">
            {items.map((item) => (
              <ListCard
                key={item.id}
                title={item.name}
                description={item.description}
                badge={<StateBadge state={item.state} />}
              />
            ))}
          </Stack>

          {pagination && pagination.totalPages > 1 && (
            <Group justify="space-between">
              <Text size="sm" c="dimmed">
                Showing {items.length} of {pagination.total}
              </Text>
              <Pagination
                total={pagination.totalPages}
                value={pagination.page}
                onChange={handlers.onPageChange}
              />
            </Group>
          )}
        </>
      )}
    </Stack>
  );
}
```

#### Toolbar Component

```typescript
// components/{feature}-toolbar.tsx
import { Group, Select, TextInput } from "@mantine/core";
import { useDebouncedCallback } from "@mantine/hooks";
import { IconSearch } from "@tabler/icons-react";
import type { StateFilter } from "@/{context}/{entity}/{feature}";

export interface FeatureToolbarProps {
  search?: string;
  state?: StateFilter;
  sortBy?: "name" | "createdAt" | "updatedAt";
  sortOrder?: "asc" | "desc";
  onSearchChange: (search: string) => void;
  onStateChange: (state: StateFilter | undefined) => void;
  onSortChange: (sortBy: "name" | "createdAt" | "updatedAt", sortOrder: "asc" | "desc") => void;
}

const stateOptions = [
  { value: "", label: "All states" },
  { value: "draft", label: "Draft" },
  { value: "published", label: "Published" },
  { value: "archived", label: "Archived" },
];

const sortOptions = [
  { value: "createdAt:desc", label: "Newest first" },
  { value: "createdAt:asc", label: "Oldest first" },
  { value: "updatedAt:desc", label: "Recently updated" },
  { value: "name:asc", label: "Name A-Z" },
  { value: "name:desc", label: "Name Z-A" },
];

export function FeatureToolbar({ search, state, sortBy = "createdAt", sortOrder = "desc", ...handlers }: FeatureToolbarProps) {
  const debouncedSearch = useDebouncedCallback((value: string) => {
    handlers.onSearchChange(value);
  }, 300);

  const handleSortChange = (value: string | null) => {
    if (!value) return;
    const [newSortBy, newSortOrder] = value.split(":") as ["name" | "createdAt" | "updatedAt", "asc" | "desc"];
    handlers.onSortChange(newSortBy, newSortOrder);
  };

  return (
    <Group gap="sm">
      <TextInput
        placeholder="Search..."
        leftSection={<IconSearch size={16} />}
        defaultValue={search}
        onChange={(e) => debouncedSearch(e.currentTarget.value)}
        style={{ flex: 1, maxWidth: 300 }}
      />
      <Select
        data={stateOptions}
        value={state ?? ""}
        onChange={(v) => handlers.onStateChange(v ? (v as StateFilter) : undefined)}
        clearable
        w={150}
      />
      <Select
        data={sortOptions}
        value={`${sortBy}:${sortOrder}`}
        onChange={handleSortChange}
        w={180}
      />
    </Group>
  );
}
```

#### Empty State Component

```typescript
// components/{feature}-empty.tsx
import { Center, Stack, Text } from "@mantine/core";
import { IconInbox } from "@tabler/icons-react";

export function FeatureEmpty() {
  return (
    <Center py="xl">
      <Stack align="center" gap="sm">
        <IconInbox size={48} color="var(--mantine-color-dimmed)" />
        <Text c="dimmed" size="lg">No items found</Text>
        <Text c="dimmed" size="sm">Create your first item to get started</Text>
      </Stack>
    </Center>
  );
}
```

### Step 5: Create Page Component

The page is the composition layer that connects hooks to components.

```typescript
// pages/{feature}.page.tsx
import { Group, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { FeatureList, useFeature } from "@/{context}/{entity}/{feature}";

export function FeaturePage() {
  const {
    items,
    pagination,
    filters,
    isLoading,
    isError,
    error,
    setPage,
    setSearch,
    setState,
    setSort,
  } = useFeature();

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Group justify="space-between" align="center">
          <Title order={2}>Feature Title</Title>
        </Group>

        <FeatureList
          items={items}
          pagination={pagination}
          filters={filters}
          isLoading={isLoading}
          isError={isError}
          error={error}
          onPageChange={setPage}
          onSearchChange={setSearch}
          onStateChange={setState}
          onSortChange={setSort}
        />
      </Stack>
    </AppLayout>
  );
}
```

### Step 6: Create Barrel Export

```typescript
// index.ts
export { FeatureList, type FeatureListProps } from "./components/{feature}-list";
export { FeatureEmpty } from "./components/{feature}-empty";
export { FeatureToolbar, type FeatureToolbarProps } from "./components/{feature}-toolbar";

export { useFeature, type StateFilter, type Filters } from "./hooks/use-{feature}";

export { FeaturePage } from "./pages/{feature}.page";

export { featureRoute, type FeatureSearch } from "./routes/{feature}.route";
```

## Testing Patterns

### Hook Tests

```typescript
// __tests__/use-{feature}.test.ts
import { renderHook, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { useFeature } from "../hooks/use-feature";

// Mock TanStack Router
vi.mock("@tanstack/react-router", () => ({
  useNavigate: () => vi.fn(),
  useSearch: () => ({ page: 1 }),
}));

// Mock API
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listEntitiesOptions: () => ({ queryKey: ["entities"], queryFn: async () => mockData }),
}));
```

### Component Tests

```typescript
// __tests__/{feature}-list.test.tsx
import { MantineProvider } from "@mantine/core";
import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { FeatureList } from "../components/{feature}-list";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("FeatureList", () => {
  it("should render items", () => {
    renderWithProviders(
      <FeatureList items={mockItems} {...defaultProps} />
    );
    expect(screen.getByText("Item Name")).toBeInTheDocument();
  });

  it("should show empty state when no items", () => {
    renderWithProviders(
      <FeatureList items={[]} {...defaultProps} />
    );
    expect(screen.getByText("No items found")).toBeInTheDocument();
  });
});
```

## State Management

### URL State (TanStack Router)

All filter, sort, and pagination state is stored in URL search params:
- Enables shareable links
- Browser back/forward navigation works
- Page refresh preserves state

### Server State (TanStack Query)

Data fetching uses TanStack Query:
- Automatic caching
- Background refetching
- Loading and error states

### Client State (Zustand)

Only use Zustand for:
- UI state that shouldn't be in URL (modals, sidebars)
- Complex cross-component state
- NOT for server data or URL-driven state

## Checklist

When creating a feature:
- [ ] Create folder structure: `components/`, `hooks/`, `pages/`, `routes/`
- [ ] Define route with Zod search schema
- [ ] Register route in `router.tsx`
- [ ] Create hook with URL state management
- [ ] Create presentational components
- [ ] Create page as composition layer
- [ ] Create barrel export in `index.ts`
- [ ] Use barrel export for all internal imports
- [ ] Run `castor app:lint -f` and `castor app:build`
- [ ] Add tests for hook and components

## Reference Files

- `app/src/authoring/rule/list/` - Complete list feature example
- `app/src/router.tsx` - Code-based router configuration
- `app/src/shared-kernel/ui/data-display/` - Shared components