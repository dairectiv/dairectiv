import { Group, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
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
