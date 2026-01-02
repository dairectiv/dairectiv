import { Group, Stack } from "@mantine/core";
import type {
  DirectiveState,
  PaginationResponse,
  RuleResponse,
} from "@shared/infrastructure/api/generated/types.gen";
import {
  type BadgeProps,
  ListCard,
  ListContainer,
  ListFilter,
  ListSearch,
  ListSort,
} from "@shared/ui/data-display";
import { IconInbox } from "@tabler/icons-react";
import { useNavigate } from "@tanstack/react-router";
import { formatDistanceToNow } from "date-fns";
import type { RulesListStateFilter } from "../hooks/use-rules-list";

function formatRelativeDate(date: Date | string): string {
  const dateObj = typeof date === "string" ? new Date(date) : date;
  return formatDistanceToNow(dateObj, { addSuffix: true });
}

const stateBadgeConfig: Record<DirectiveState, BadgeProps> = {
  draft: { label: "Draft", color: "yellow" },
  published: { label: "Published", color: "green" },
  archived: { label: "Archived", color: "gray" },
  deleted: { label: "Deleted", color: "red" },
};

const stateFilterOptions = [
  { value: "draft", label: "Draft" },
  { value: "published", label: "Published" },
  { value: "archived", label: "Archived" },
];

const sortOptions = [
  { value: "updatedAt:desc", label: "Recently updated" },
  { value: "createdAt:desc", label: "Newest first" },
  { value: "createdAt:asc", label: "Oldest first" },
  { value: "name:asc", label: "Name A-Z" },
  { value: "name:desc", label: "Name Z-A" },
];

export interface RulesListProps {
  rules: RuleResponse[];
  pagination?: PaginationResponse;
  filters: {
    search?: string;
    state?: RulesListStateFilter;
    sortBy?: "name" | "createdAt" | "updatedAt";
    sortOrder?: "asc" | "desc";
  };
  isLoading: boolean;
  isError: boolean;
  error?: Error | null;
  onPageChange: (page: number) => void;
  onSearchChange: (search: string) => void;
  onStateChange: (state: RulesListStateFilter | undefined) => void;
  onSortChange: (sortBy: "name" | "createdAt" | "updatedAt", sortOrder: "asc" | "desc") => void;
}

export function RulesList({
  rules,
  pagination,
  filters,
  isLoading,
  isError,
  error,
  onPageChange,
  onSearchChange,
  onStateChange,
  onSortChange,
}: RulesListProps) {
  const navigate = useNavigate();

  const handleSortChange = (sort: string) => {
    const [sortBy, sortOrder] = sort.split(":") as [
      "name" | "createdAt" | "updatedAt",
      "asc" | "desc",
    ];
    onSortChange(sortBy, sortOrder);
  };

  const currentSort = `${filters.sortBy ?? "updatedAt"}:${filters.sortOrder ?? "desc"}`;

  return (
    <Stack gap="md">
      <Group gap="sm">
        <ListSearch
          value={filters.search}
          placeholder="Search rules..."
          onChange={onSearchChange}
        />
        <ListFilter
          value={filters.state}
          options={stateFilterOptions}
          placeholder="Filter by state"
          onChange={(value) => onStateChange(value as RulesListStateFilter | undefined)}
        />
        <ListSort value={currentSort} options={sortOptions} onChange={handleSortChange} />
      </Group>

      <ListContainer
        isLoading={isLoading}
        isError={isError}
        errorMessage="Failed to load rules"
        errorDetails={error?.message}
        isEmpty={rules.length === 0}
        empty={{
          icon: IconInbox,
          title: "No rules found",
          subtitle: "Create your first rule to get started",
        }}
        pagination={
          pagination
            ? {
                page: pagination.page,
                totalPages: pagination.totalPages,
                total: pagination.total,
              }
            : undefined
        }
        onPageChange={onPageChange}
        itemCount={rules.length}
        itemLabel="rules"
      >
        {rules.map((rule) => (
          <ListCard
            key={rule.id}
            title={rule.name}
            description={rule.description}
            metadata={formatRelativeDate(rule.updatedAt)}
            badge={stateBadgeConfig[rule.state]}
            onClick={() =>
              navigate({
                to: "/authoring/rules/$ruleId",
                params: { ruleId: rule.id },
              })
            }
          />
        ))}
      </ListContainer>
    </Stack>
  );
}
