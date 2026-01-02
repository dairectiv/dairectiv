import { Group, Stack } from "@mantine/core";
import type {
  DirectiveState,
  PaginationResponse,
  WorkflowResponse,
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
import { formatDistanceToNow } from "date-fns";
import type { WorkflowsListStateFilter } from "../hooks/use-workflows-list";

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

export interface WorkflowsListProps {
  workflows: WorkflowResponse[];
  pagination?: PaginationResponse;
  filters: {
    search?: string;
    state?: WorkflowsListStateFilter;
    sortBy?: "name" | "createdAt" | "updatedAt";
    sortOrder?: "asc" | "desc";
  };
  isLoading: boolean;
  isError: boolean;
  error?: Error | null;
  onPageChange: (page: number) => void;
  onSearchChange: (search: string) => void;
  onStateChange: (state: WorkflowsListStateFilter | undefined) => void;
  onSortChange: (sortBy: "name" | "createdAt" | "updatedAt", sortOrder: "asc" | "desc") => void;
}

export function WorkflowsList({
  workflows,
  pagination,
  filters,
  isLoading,
  isError,
  error,
  onPageChange,
  onSearchChange,
  onStateChange,
  onSortChange,
}: WorkflowsListProps) {
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
          placeholder="Search workflows..."
          onChange={onSearchChange}
        />
        <ListFilter
          value={filters.state}
          options={stateFilterOptions}
          placeholder="Filter by state"
          onChange={(value) => onStateChange(value as WorkflowsListStateFilter | undefined)}
        />
        <ListSort value={currentSort} options={sortOptions} onChange={handleSortChange} />
      </Group>

      <ListContainer
        isLoading={isLoading}
        isError={isError}
        errorMessage="Failed to load workflows"
        errorDetails={error?.message}
        isEmpty={workflows.length === 0}
        empty={{
          icon: IconInbox,
          title: "No workflows found",
          subtitle: "Create your first workflow to get started",
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
        itemCount={workflows.length}
        itemLabel="workflows"
      >
        {workflows.map((workflow) => (
          <ListCard
            key={workflow.id}
            title={workflow.name}
            description={workflow.description}
            metadata={formatRelativeDate(workflow.updatedAt)}
            badge={stateBadgeConfig[workflow.state]}
          />
        ))}
      </ListContainer>
    </Stack>
  );
}
