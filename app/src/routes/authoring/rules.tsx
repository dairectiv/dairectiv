import { Badge, Center, Group, Loader, Pagination, Stack, Table, Text, Title } from "@mantine/core";
import { listRulesOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import type { DirectiveState } from "@shared/infrastructure/api/generated/types.gen";
import { AppLayout } from "@shared/ui/layout";
import { IconInbox } from "@tabler/icons-react";
import { useQuery } from "@tanstack/react-query";
import { createFileRoute, Link, useNavigate, useSearch } from "@tanstack/react-router";
import { z } from "zod";
import classes from "./rules.module.css";

const searchSchema = z.object({
  page: z.number().min(1).optional().default(1),
});

export const Route = createFileRoute("/authoring/rules")({
  component: RulesListPage,
  validateSearch: searchSchema,
});

const stateColors: Record<DirectiveState, string> = {
  draft: "yellow",
  published: "green",
  archived: "gray",
  deleted: "red",
};

const stateLabels: Record<DirectiveState, string> = {
  draft: "Draft",
  published: "Published",
  archived: "Archived",
  deleted: "Deleted",
};

function truncateDescription(description: string, maxLength = 100): string {
  if (description.length <= maxLength) {
    return description;
  }
  return `${description.slice(0, maxLength).trim()}...`;
}

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
            <Table.ScrollContainer minWidth={600}>
              <Table striped highlightOnHover>
                <Table.Thead>
                  <Table.Tr>
                    <Table.Th>Name</Table.Th>
                    <Table.Th>Description</Table.Th>
                    <Table.Th w={100}>Status</Table.Th>
                  </Table.Tr>
                </Table.Thead>
                <Table.Tbody>
                  {data.items.map((rule) => (
                    <Table.Tr key={rule.id} className={classes.row}>
                      <Table.Td>
                        <Link
                          to="/authoring/rules/$ruleId"
                          params={{ ruleId: rule.id }}
                          className={classes.link}
                        >
                          <Text fw={500}>{rule.name}</Text>
                        </Link>
                      </Table.Td>
                      <Table.Td>
                        <Text size="sm" c="dimmed">
                          {truncateDescription(rule.description)}
                        </Text>
                      </Table.Td>
                      <Table.Td>
                        <Badge color={stateColors[rule.state]} variant="light" size="sm">
                          {stateLabels[rule.state]}
                        </Badge>
                      </Table.Td>
                    </Table.Tr>
                  ))}
                </Table.Tbody>
              </Table>
            </Table.ScrollContainer>

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
