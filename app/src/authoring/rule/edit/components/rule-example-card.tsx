import { ActionIcon, Badge, Card, Group, Stack, Text } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type {
  RuleExampleResponse,
  UpdateRuleExamplePayload,
} from "@shared/infrastructure/api/generated/types.gen";
import { ConfirmModal } from "@shared/ui/feedback";
import { IconCheck, IconEdit, IconTrash, IconX } from "@tabler/icons-react";
import { RuleExampleForm, type RuleExampleFormValues } from "./rule-example-form";

export interface RuleExampleCardProps {
  example: RuleExampleResponse;
  onUpdate: (exampleId: string, payload: UpdateRuleExamplePayload) => void;
  onRemove: (exampleId: string) => void;
  isUpdating?: boolean;
  isRemoving?: boolean;
}

export function RuleExampleCard({
  example,
  onUpdate,
  onRemove,
  isUpdating = false,
  isRemoving = false,
}: RuleExampleCardProps) {
  const [isEditing, { open: startEditing, close: stopEditing }] = useDisclosure(false);
  const [deleteModalOpened, { open: openDeleteModal, close: closeDeleteModal }] =
    useDisclosure(false);

  const handleSubmit = (values: RuleExampleFormValues) => {
    const payload: UpdateRuleExamplePayload = {
      good: values.good || null,
      bad: values.bad || null,
      explanation: values.explanation || null,
    };
    onUpdate(example.id, payload);
    stopEditing();
  };

  const handleConfirmDelete = () => {
    onRemove(example.id);
    closeDeleteModal();
  };

  if (isEditing) {
    return (
      <Card withBorder p="md">
        <RuleExampleForm
          initialValues={{
            good: example.good ?? "",
            bad: example.bad ?? "",
            explanation: example.explanation ?? "",
          }}
          onSubmit={handleSubmit}
          onCancel={stopEditing}
          isLoading={isUpdating}
          submitLabel="Update"
        />
      </Card>
    );
  }

  return (
    <>
      <Card withBorder p="md">
        <Stack gap="sm">
          <Group justify="space-between" align="flex-start">
            <Stack gap="sm" style={{ flex: 1 }}>
              {example.good && (
                <Group gap="xs" align="flex-start">
                  <Badge
                    size="sm"
                    color="green"
                    variant="light"
                    leftSection={<IconCheck size={12} />}
                  >
                    Good
                  </Badge>
                  <Text size="sm" style={{ flex: 1, whiteSpace: "pre-wrap" }}>
                    {example.good}
                  </Text>
                </Group>
              )}
              {example.bad && (
                <Group gap="xs" align="flex-start">
                  <Badge size="sm" color="red" variant="light" leftSection={<IconX size={12} />}>
                    Bad
                  </Badge>
                  <Text size="sm" style={{ flex: 1, whiteSpace: "pre-wrap" }}>
                    {example.bad}
                  </Text>
                </Group>
              )}
              {example.explanation && (
                <Text size="xs" c="dimmed" fs="italic">
                  {example.explanation}
                </Text>
              )}
            </Stack>
            <Group gap="xs">
              <ActionIcon
                variant="subtle"
                color="gray"
                onClick={startEditing}
                disabled={isRemoving}
                aria-label="Edit example"
              >
                <IconEdit size={16} />
              </ActionIcon>
              <ActionIcon
                variant="subtle"
                color="red"
                onClick={openDeleteModal}
                loading={isRemoving}
                aria-label="Delete example"
              >
                <IconTrash size={16} />
              </ActionIcon>
            </Group>
          </Group>
        </Stack>
      </Card>

      <ConfirmModal
        opened={deleteModalOpened}
        onClose={closeDeleteModal}
        onConfirm={handleConfirmDelete}
        title="Remove Example"
        message="Are you sure you want to remove this example? This action cannot be undone."
        confirmLabel="Remove"
        confirmColor="red"
        isLoading={isRemoving}
      />
    </>
  );
}
