import { Button, Group, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { IconPlus } from "@tabler/icons-react";
import { Link } from "@tanstack/react-router";
import { useWorkflowsList, WorkflowsList } from "@/authoring/workflow/list";

export function WorkflowsListPage() {
  const {
    workflows,
    pagination,
    filters,
    isLoading,
    isError,
    error,
    setPage,
    setSearch,
    setState,
    setSort,
  } = useWorkflowsList();

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Group justify="space-between" align="center">
          <Title order={2}>Workflows</Title>
          <Button
            component={Link}
            to="/authoring/workflows/new"
            leftSection={<IconPlus size={16} />}
          >
            Create workflow
          </Button>
        </Group>

        <WorkflowsList
          workflows={workflows}
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
