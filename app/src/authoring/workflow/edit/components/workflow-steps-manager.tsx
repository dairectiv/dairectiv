import {
  closestCenter,
  DndContext,
  type DragEndEvent,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from "@dnd-kit/core";
import {
  SortableContext,
  sortableKeyboardCoordinates,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import { Alert, Button, Card, Collapse, Group, Stack, Title } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type {
  AddWorkflowStepPayload,
  StepResponse,
  UpdateWorkflowStepPayload,
} from "@shared/infrastructure/api/generated/types.gen";
import { IconInfoCircle, IconPlus } from "@tabler/icons-react";
import { useState } from "react";
import { useAddWorkflowStep } from "../hooks/use-add-workflow-step";
import { useMoveWorkflowStep } from "../hooks/use-move-workflow-step";
import { useRemoveWorkflowStep } from "../hooks/use-remove-workflow-step";
import { useUpdateWorkflowStep } from "../hooks/use-update-workflow-step";
import { SortableWorkflowStepCard } from "./sortable-workflow-step-card";
import { WorkflowStepForm, type WorkflowStepFormValues } from "./workflow-step-form";

export interface WorkflowStepsManagerProps {
  workflowId: string;
  steps: StepResponse[];
}

export function WorkflowStepsManager({ workflowId, steps }: WorkflowStepsManagerProps) {
  const [addFormOpened, { open: openAddForm, close: closeAddForm }] = useDisclosure(false);
  const [formKey, setFormKey] = useState(0);

  const { addStep, isAdding } = useAddWorkflowStep(workflowId, {
    onSuccess: () => {
      closeAddForm();
      setFormKey((k) => k + 1);
    },
  });

  const { updateStep, isUpdating } = useUpdateWorkflowStep(workflowId);
  const { removeStep, isRemoving } = useRemoveWorkflowStep(workflowId);
  const { moveStep } = useMoveWorkflowStep(workflowId);

  // Sort steps by order
  const sortedSteps = [...steps].sort((a, b) => a.order - b.order);

  // Set up dnd-kit sensors
  const sensors = useSensors(
    useSensor(PointerSensor, {
      activationConstraint: {
        distance: 5,
      },
    }),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    }),
  );

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

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;

    if (!over || active.id === over.id) {
      return;
    }

    const activeId = active.id as string;
    const overId = over.id as string;

    // Find the indices
    const oldIndex = sortedSteps.findIndex((step) => step.id === activeId);
    const newIndex = sortedSteps.findIndex((step) => step.id === overId);

    if (oldIndex === -1 || newIndex === -1) {
      return;
    }

    // Determine the afterStepId based on the new position
    let afterStepId: string | null = null;

    if (newIndex === 0) {
      // Moving to first position
      afterStepId = null;
    } else if (newIndex > oldIndex) {
      // Moving down - place after the target step
      afterStepId = sortedSteps[newIndex].id;
    } else {
      // Moving up - place after the step before the target position
      afterStepId = newIndex > 0 ? sortedSteps[newIndex - 1].id : null;
    }

    moveStep(activeId, afterStepId);
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
                key={formKey}
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

        <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
          <SortableContext
            items={sortedSteps.map((s) => s.id)}
            strategy={verticalListSortingStrategy}
          >
            <Stack gap="sm">
              {sortedSteps.map((step, index) => (
                <SortableWorkflowStepCard
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
          </SortableContext>
        </DndContext>
      </Stack>
    </Card>
  );
}
