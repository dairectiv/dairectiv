import { Center, Group, Loader, Pagination, Stack, Text, Title } from "@mantine/core";
import { listRulesOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { DirectiveListItem } from "@shared/ui/data-display";
import { AppLayout } from "@shared/ui/layout";
import { IconInbox } from "@tabler/icons-react";
import { useQuery } from "@tanstack/react-query";
import { createFileRoute, useNavigate, useSearch } from "@tanstack/react-router";
import { z } from "zod";

const searchSchema = z.object({
  page: z.number().min(1).optional().default(1),
});

export const Route = createFileRoute("/authoring/rules")({
  component: RulesListPage,
  validateSearch: searchSchema,
});

function RulesListPage() {
  const navigate = useNavigate();
  const { page } = useSearch({ from: "/authoring/rules" });

  const { data, isLoading, isError, error } = useQuery(
    listRulesOptions({
      query: { page, limit: 10 },
    }),
  );

  const handlePageChange = (newPage: number) => {
    navigate({
      to: "/authoring/rules",
      search: { page: newPage },
    });
  };

  const handleRuleClick = (ruleId: string) => {
    navigate({
      to: "/authoring/rules/$ruleId",
      params: { ruleId },
    });
  };

  return (
    <AppLayout>
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

        {!isLoading && !isError && data?.items.length === 0 && (
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

        {!isLoading && !isError && data && data.items.length > 0 && (
          <Stack gap="md">
            <Stack gap="xs">
              {data.items.map((rule) => (
                <DirectiveListItem
                  key={rule.id}
                  name={rule.name}
                  description={rule.description}
                  state={rule.state}
                  onClick={() => handleRuleClick(rule.id)}
                />
              ))}
            </Stack>

            {data.pagination.totalPages > 1 && (
              <Group justify="space-between" align="center">
                <Text size="sm" c="dimmed">
                  Showing {data.items.length} of {data.pagination.total} rules
                </Text>
                <Pagination
                  total={data.pagination.totalPages}
                  value={data.pagination.page}
                  onChange={handlePageChange}
                />
              </Group>
            )}
          </Stack>
        )}
      </Stack>
    </AppLayout>
  );
}
