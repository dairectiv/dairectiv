import { Button, Group, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { IconPlus } from "@tabler/icons-react";
import { Link } from "@tanstack/react-router";
import { RulesList, useRulesList } from "@/authoring/rule/list";

export function RulesListPage() {
  const {
    rules,
    pagination,
    filters,
    isLoading,
    isError,
    error,
    setPage,
    setSearch,
    setState,
    setSort,
  } = useRulesList();

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Group justify="space-between" align="center">
          <Title order={2}>Rules</Title>
          <Button component={Link} to="/authoring/rules/new" leftSection={<IconPlus size={16} />}>
            Create rule
          </Button>
        </Group>

        <RulesList
          rules={rules}
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
