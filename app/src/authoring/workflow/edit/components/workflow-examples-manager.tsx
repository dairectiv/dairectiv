import { Alert, Button, Card, Collapse, Stack, Text, Title } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type { WorkflowExampleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { IconInfoCircle, IconPlus } from "@tabler/icons-react";
import { useState } from "react";
import { useAddWorkflowExample } from "../hooks/use-add-workflow-example";
import { useRemoveWorkflowExample } from "../hooks/use-remove-workflow-example";
import { useUpdateWorkflowExample } from "../hooks/use-update-workflow-example";
import { WorkflowExampleCard } from "./workflow-example-card";
import { WorkflowExampleForm, type WorkflowExampleFormValues } from "./workflow-example-form";

export interface WorkflowExamplesManagerProps {
  workflowId: string;
  examples: WorkflowExampleResponse[];
}

export function WorkflowExamplesManager({ workflowId, examples }: WorkflowExamplesManagerProps) {
  const [addFormOpened, { open: openAddForm, close: closeAddForm }] = useDisclosure(false);
  const [formKey, setFormKey] = useState(0);

  const { addExample, isAdding } = useAddWorkflowExample(workflowId, {
    onSuccess: () => {
      closeAddForm();
      setFormKey((k) => k + 1);
    },
  });

  const { updateExample, isUpdating } = useUpdateWorkflowExample(workflowId);
  const { removeExample, isRemoving } = useRemoveWorkflowExample(workflowId);

  const handleAddExample = (values: WorkflowExampleFormValues) => {
    addExample({
      scenario: values.scenario,
      input: values.input,
      output: values.output,
      explanation: values.explanation || null,
    });
  };

  const handleUpdateExample = (exampleId: string, values: WorkflowExampleFormValues) => {
    updateExample(exampleId, {
      scenario: values.scenario,
      input: values.input,
      output: values.output,
      explanation: values.explanation || null,
    });
  };

  const handleRemoveExample = (exampleId: string) => {
    removeExample(exampleId);
  };

  return (
    <Stack gap="md">
      <Title order={3}>Examples</Title>

      {examples.length === 0 && !addFormOpened && (
        <Alert icon={<IconInfoCircle size={16} />} color="gray">
          No examples yet. Add examples to show how this workflow should be used.
        </Alert>
      )}

      {examples.map((example, index) => (
        <WorkflowExampleCard
          key={example.id}
          example={example}
          exampleNumber={index + 1}
          onUpdate={handleUpdateExample}
          onRemove={handleRemoveExample}
          isUpdating={isUpdating}
          isRemoving={isRemoving}
        />
      ))}

      <Collapse in={addFormOpened}>
        <Card withBorder p="md">
          <Stack gap="md">
            <Text fw={500}>New Example</Text>
            <WorkflowExampleForm
              key={formKey}
              onSubmit={handleAddExample}
              onCancel={closeAddForm}
              isLoading={isAdding}
              submitLabel="Add Example"
            />
          </Stack>
        </Card>
      </Collapse>

      {!addFormOpened && (
        <Button variant="light" leftSection={<IconPlus size={16} />} onClick={openAddForm}>
          Add Example
        </Button>
      )}
    </Stack>
  );
}
