import { ActionIcon, Badge, Card, Group, Stack, Text } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type {
  StepResponse,
  UpdateWorkflowStepPayload,
} from "@shared/infrastructure/api/generated/types.gen";
import { ConfirmModal } from "@shared/ui/feedback";
import { IconEdit, IconTrash } from "@tabler/icons-react";
import { useState } from "react";
import { WorkflowStepForm, type WorkflowStepFormValues } from "./workflow-step-form";

export interface WorkflowStepCardProps {
  step: StepResponse;
  stepNumber: number;
  onUpdate: (stepId: string, payload: UpdateWorkflowStepPayload) => void;
  onRemove: (stepId: string) => void;
  isUpdating?: boolean;
  isRemoving?: boolean;
}

export function WorkflowStepCard({
  step,
  stepNumber,
  onUpdate,
  onRemove,
  isUpdating = false,
  isRemoving = false,
}: WorkflowStepCardProps) {
  const [isEditing, setIsEditing] = useState(false);
  const [deleteModalOpened, { open: openDeleteModal, close: closeDeleteModal }] =
    useDisclosure(false);

  const handleEdit = () => {
    setIsEditing(true);
  };

  const handleCancelEdit = () => {
    setIsEditing(false);
  };

  const handleUpdate = (values: WorkflowStepFormValues) => {
    onUpdate(step.id, { content: values.content });
    setIsEditing(false);
  };

  const handleConfirmDelete = () => {
    onRemove(step.id);
    closeDeleteModal();
  };

  if (isEditing) {
    return (
      <Card withBorder p="md" bg="gray.0">
        <Stack gap="sm">
          <Group gap="xs">
            <Badge size="sm" variant="filled">
              Step {stepNumber}
            </Badge>
          </Group>
          <WorkflowStepForm
            initialValues={{ content: step.content }}
            onSubmit={handleUpdate}
            onCancel={handleCancelEdit}
            isLoading={isUpdating}
            submitLabel="Save"
          />
        </Stack>
      </Card>
    );
  }

  return (
    <>
      <Card withBorder p="md">
        <Stack gap="sm">
          <Group justify="space-between" align="flex-start">
            <Group gap="xs">
              <Badge size="sm" variant="filled">
                Step {stepNumber}
              </Badge>
            </Group>
            <Group gap={4}>
              <ActionIcon variant="subtle" size="sm" onClick={handleEdit} aria-label="Edit step">
                <IconEdit size={16} />
              </ActionIcon>
              <ActionIcon
                variant="subtle"
                size="sm"
                color="red"
                onClick={openDeleteModal}
                loading={isRemoving}
                aria-label="Delete step"
              >
                <IconTrash size={16} />
              </ActionIcon>
            </Group>
          </Group>
          <Text size="sm" style={{ whiteSpace: "pre-wrap" }}>
            {step.content}
          </Text>
        </Stack>
      </Card>

      <ConfirmModal
        opened={deleteModalOpened}
        onClose={closeDeleteModal}
        onConfirm={handleConfirmDelete}
        title="Delete Step"
        message="Are you sure you want to delete this step? This action cannot be undone."
        confirmLabel="Delete"
        confirmColor="red"
        isLoading={isRemoving}
      />
    </>
  );
}
