import { Center, Group, Loader, Pagination, Stack, Text, Title } from "@mantine/core";
import type {
  PaginationResponse,
  RuleResponse,
} from "@shared/infrastructure/api/generated/types.gen";
import { ListCard, StateBadge } from "@shared/ui/data-display";
import { IconInbox } from "@tabler/icons-react";

export interface RulesListProps {
  rules: RuleResponse[];
  pagination?: PaginationResponse;
  isLoading: boolean;
  isError: boolean;
  error?: Error | null;
  onPageChange: (page: number) => void;
}

export function RulesList({
  rules,
  pagination,
  isLoading,
  isError,
  error,
  onPageChange,
}: RulesListProps) {
  return (
    <Stack gap="lg" py="md">
      <Group justify="space-between" align="center">
        <Title order={2}>Rules</Title>
      </Group>

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

      {!isLoading && !isError && rules.length === 0 && (
        <Center py="xl">
          <Stack align="center" gap="sm">
            <IconInbox size={48} color="var(--mantine-color-dimmed)" />
            <Text c="dimmed" size="lg">
              No rules found
            </Text>
            <Text c="dimmed" size="sm">
              Create your first rule to get started
            </Text>
          </Stack>
        </Center>
      )}

      {!isLoading && !isError && rules.length > 0 && (
        <Stack gap="md">
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
        </Stack>
      )}
    </Stack>
  );
}
