import { Center, Group, Loader, Pagination, Stack, Text } from "@mantine/core";
import type {
  PaginationResponse,
  RuleResponse,
} from "@shared/infrastructure/api/generated/types.gen";
import { ListCard, StateBadge } from "@shared/ui/data-display";
import type { RulesListStateFilter } from "../hooks/use-rules-list";
import { RulesListEmpty } from "./rules-list-empty";
import { RulesListToolbar } from "./rules-list-toolbar";

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
  return (
    <Stack gap="md">
      <RulesListToolbar
        search={filters.search}
        state={filters.state}
        sortBy={filters.sortBy}
        sortOrder={filters.sortOrder}
        onSearchChange={onSearchChange}
        onStateChange={onStateChange}
        onSortChange={onSortChange}
      />

      {isLoading && (
        <Center py="xl">
          <Loader size="lg" />
        </Center>
      )}

      {isError && (
        <Center py="xl">
          <Stack align="center" gap="sm">
            <Text c="red" size="lg">
              Failed to load rules
            </Text>
            <Text c="dimmed" size="sm">
              {error?.message ?? "An unexpected error occurred"}
            </Text>
          </Stack>
        </Center>
      )}

      {!isLoading && !isError && rules.length === 0 && <RulesListEmpty />}

      {!isLoading && !isError && rules.length > 0 && (
        <>
          <Stack gap="xs">
            {rules.map((rule) => (
              <ListCard
                key={rule.id}
                title={rule.name}
                description={rule.description}
                badge={<StateBadge state={rule.state} />}
              />
            ))}
          </Stack>

          {pagination && pagination.totalPages > 1 && (
            <Group justify="space-between" align="center">
              <Text size="sm" c="dimmed">
                Showing {rules.length} of {pagination.total} rules
              </Text>
              <Pagination
                total={pagination.totalPages}
                value={pagination.page}
                onChange={onPageChange}
              />
            </Group>
          )}
        </>
      )}
    </Stack>
  );
}
