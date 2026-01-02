import { Center, Stack, Text } from "@mantine/core";
import { IconInbox } from "@tabler/icons-react";

export function RulesListEmpty() {
  return (
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
  );
}
