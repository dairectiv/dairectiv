import { Alert, Button, Card, Collapse, Group, Stack, Title } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type {
  AddWorkflowStepPayload,
  StepResponse,
  UpdateWorkflowStepPayload,
} from "@shared/infrastructure/api/generated/types.gen";
import { IconInfoCircle, IconPlus } from "@tabler/icons-react";
import { useAddWorkflowStep } from "../hooks/use-add-workflow-step";
import { useRemoveWorkflowStep } from "../hooks/use-remove-workflow-step";
import { useUpdateWorkflowStep } from "../hooks/use-update-workflow-step";
import { WorkflowStepCard } from "./workflow-step-card";
import { WorkflowStepForm, type WorkflowStepFormValues } from "./workflow-step-form";

export interface WorkflowStepsManagerProps {
  workflowId: string;
  steps: StepResponse[];
}

export function WorkflowStepsManager({ workflowId, steps }: WorkflowStepsManagerProps) {
  const [addFormOpened, { open: openAddForm, close: closeAddForm }] = useDisclosure(false);

  const { addStep, isAdding } = useAddWorkflowStep(workflowId, {
    onSuccess: () => {
      closeAddForm();
    },
  });

  const { updateStep, isUpdating } = useUpdateWorkflowStep(workflowId);
  const { removeStep, isRemoving } = useRemoveWorkflowStep(workflowId);

  // Sort steps by order
  const sortedSteps = [...steps].sort((a, b) => a.order - b.order);

  const handleAddStep = (values: WorkflowStepFormValues) => {
    // Add step at the end (after the last step)
    const lastStep = sortedSteps[sortedSteps.length - 1];
    const payload: AddWorkflowStepPayload = {
      content: values.content,
      afterStepId: lastStep?.id ?? null,
    };
    addStep(payload);
  };

  const handleUpdateStep = (stepId: string, payload: UpdateWorkflowStepPayload) => {
    updateStep(stepId, payload);
  };

  const handleRemoveStep = (stepId: string) => {
    removeStep(stepId);
  };

  return (
    <Card withBorder p="lg">
      <Stack gap="md">
        <Group justify="space-between" align="center">
          <Title order={4}>Steps ({steps.length})</Title>
          {!addFormOpened && (
            <Button
              leftSection={<IconPlus size={16} />}
              variant="light"
              size="sm"
              onClick={openAddForm}
            >
              Add Step
            </Button>
          )}
        </Group>

        <Collapse in={addFormOpened}>
          <Card withBorder p="md" bg="gray.0">
            <Stack gap="sm">
              <Title order={5}>New Step</Title>
              <WorkflowStepForm
                onSubmit={handleAddStep}
                onCancel={closeAddForm}
                isLoading={isAdding}
                submitLabel="Add Step"
              />
            </Stack>
          </Card>
        </Collapse>

        {steps.length === 0 && !addFormOpened && (
          <Alert icon={<IconInfoCircle size={16} />} color="gray">
            No steps defined yet. Add steps to guide the workflow execution.
          </Alert>
        )}

        <Stack gap="sm">
          {sortedSteps.map((step, index) => (
            <WorkflowStepCard
              key={step.id}
              step={step}
              stepNumber={index + 1}
              onUpdate={handleUpdateStep}
              onRemove={handleRemoveStep}
              isUpdating={isUpdating}
              isRemoving={isRemoving}
            />
          ))}
        </Stack>
      </Stack>
    </Card>
  );
}
