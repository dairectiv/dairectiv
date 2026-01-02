import { Badge, Card, Center, Group, Loader, SimpleGrid, Stack, Text, Title } from "@mantine/core";
import { listRulesOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import type { DirectiveState } from "@shared/infrastructure/api/generated/types.gen";
import { AppLayout } from "@shared/ui/layout";
import { IconFileText, IconInbox } from "@tabler/icons-react";
import { useQuery } from "@tanstack/react-query";
import { createFileRoute, Link } from "@tanstack/react-router";
import classes from "./rules.module.css";

export const Route = createFileRoute("/authoring/rules")({
  component: RulesListPage,
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

function truncateDescription(description: string, maxLength = 120): string {
  if (description.length <= maxLength) {
    return description;
  }
  return `${description.slice(0, maxLength).trim()}...`;
}

function RulesListPage() {
  const { data, isLoading, isError, error } = useQuery(listRulesOptions());

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
          <SimpleGrid cols={{ base: 1, sm: 2, lg: 3 }} spacing="md">
            {data.items.map((rule) => (
              <Link
                key={rule.id}
                to="/authoring/rules/$ruleId"
                params={{ ruleId: rule.id }}
                className={classes.cardLink}
              >
                <Card shadow="sm" padding="lg" radius="md" withBorder className={classes.card}>
                  <Stack gap="sm">
                    <Group justify="space-between" wrap="nowrap">
                      <Group gap="xs" wrap="nowrap" style={{ overflow: "hidden" }}>
                        <IconFileText size={20} />
                        <Text fw={500} truncate>
                          {rule.name}
                        </Text>
                      </Group>
                      <Badge color={stateColors[rule.state]} variant="light" size="sm">
                        {stateLabels[rule.state]}
                      </Badge>
                    </Group>
                    <Text size="sm" c="dimmed" lineClamp={3}>
                      {truncateDescription(rule.description)}
                    </Text>
                  </Stack>
                </Card>
              </Link>
            ))}
          </SimpleGrid>
        )}
      </Stack>
    </AppLayout>
  );
}
