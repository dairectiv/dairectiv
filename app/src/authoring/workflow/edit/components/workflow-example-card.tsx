import { ActionIcon, Badge, Card, Group, Stack, Text } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type { WorkflowExampleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { ConfirmModal } from "@shared/ui/feedback";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import { useState } from "react";
import { WorkflowExampleForm, type WorkflowExampleFormValues } from "./workflow-example-form";

export interface WorkflowExampleCardProps {
  example: WorkflowExampleResponse;
  exampleNumber: number;
  onUpdate: (exampleId: string, values: WorkflowExampleFormValues) => void;
  onRemove: (exampleId: string) => void;
  isUpdating?: boolean;
  isRemoving?: boolean;
}

export function WorkflowExampleCard({
  example,
  exampleNumber,
  onUpdate,
  onRemove,
  isUpdating = false,
  isRemoving = false,
}: WorkflowExampleCardProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [deleteModalOpened, { open: openDeleteModal, close: closeDeleteModal }] =
    useDisclosure(false);

  const handleUpdate = (values: WorkflowExampleFormValues) => {
    onUpdate(example.id, values);
    setIsEditing(false);
  };

  const handleDelete = () => {
    onRemove(example.id);
    closeDeleteModal();
  };

  if (isEditing) {
    return (
      <Card withBorder p="md">
        <WorkflowExampleForm
          initialValues={{
            scenario: example.scenario,
            input: example.input,
            output: example.output,
            explanation: example.explanation ?? "",
          }}
          onSubmit={handleUpdate}
          onCancel={() => setIsEditing(false)}
          isLoading={isUpdating}
        />
      </Card>
    );
  }

  return (
    <>
      <Card withBorder p="md">
        <Stack gap="sm">
          <Group justify="space-between" align="flex-start">
            <Badge variant="light" size="sm">
              Example {exampleNumber}
            </Badge>
            <Group gap="xs">
              <ActionIcon
                variant="subtle"
                color="gray"
                aria-label="Edit example"
                onClick={() => setIsEditing(true)}
              >
                <IconEdit size={16} />
              </ActionIcon>
              <ActionIcon
                variant="subtle"
                color="red"
                aria-label="Delete example"
                onClick={openDeleteModal}
                loading={isRemoving}
              >
                <IconTrash size={16} />
              </ActionIcon>
            </Group>
          </Group>

          <Stack gap="xs">
            <div>
              <Text size="xs" fw={600} c="dimmed">
                Scenario
              </Text>
              <Text size="sm" style={{ whiteSpace: "pre-wrap" }}>
                {example.scenario}
              </Text>
            </div>

            <div>
              <Text size="xs" fw={600} c="dimmed">
                Input
              </Text>
              <Text size="sm" style={{ whiteSpace: "pre-wrap" }}>
                {example.input}
              </Text>
            </div>

            <div>
              <Text size="xs" fw={600} c="dimmed">
                Output
              </Text>
              <Text size="sm" style={{ whiteSpace: "pre-wrap" }}>
                {example.output}
              </Text>
            </div>

            {example.explanation && (
              <div>
                <Text size="xs" fw={600} c="dimmed">
                  Explanation
                </Text>
                <Text size="sm" c="dimmed" style={{ whiteSpace: "pre-wrap" }}>
                  {example.explanation}
                </Text>
              </div>
            )}
          </Stack>
        </Stack>
      </Card>

      <ConfirmModal
        opened={deleteModalOpened}
        onClose={closeDeleteModal}
        onConfirm={handleDelete}
        title="Delete Example"
        message="Are you sure you want to delete this example? This action cannot be undone."
        confirmLabel="Delete"
        confirmColor="red"
        isLoading={isRemoving}
      />
    </>
  );
}
