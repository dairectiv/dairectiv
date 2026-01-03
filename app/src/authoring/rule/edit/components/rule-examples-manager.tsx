import { Alert, Button, Card, Collapse, Group, Stack, Title } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type {
  AddRuleExamplePayload,
  RuleExampleResponse,
  UpdateRuleExamplePayload,
} from "@shared/infrastructure/api/generated/types.gen";
import { IconInfoCircle, IconPlus } from "@tabler/icons-react";
import { useState } from "react";
import { useAddRuleExample } from "../hooks/use-add-rule-example";
import { useRemoveRuleExample } from "../hooks/use-remove-rule-example";
import { useUpdateRuleExample } from "../hooks/use-update-rule-example";
import { RuleExampleCard } from "./rule-example-card";
import { RuleExampleForm, type RuleExampleFormValues } from "./rule-example-form";

export interface RuleExamplesManagerProps {
  ruleId: string;
  examples: RuleExampleResponse[];
}

export function RuleExamplesManager({ ruleId, examples }: RuleExamplesManagerProps) {
  const [addFormOpened, { open: openAddForm, close: closeAddForm }] = useDisclosure(false);
  const [formKey, setFormKey] = useState(0);

  const { addExample, isAdding } = useAddRuleExample(ruleId, {
    onSuccess: () => {
      closeAddForm();
      setFormKey((k) => k + 1);
    },
  });

  const { updateExample, isUpdating } = useUpdateRuleExample(ruleId);
  const { removeExample, isRemoving } = useRemoveRuleExample(ruleId);

  const handleAddExample = (values: RuleExampleFormValues) => {
    const payload: AddRuleExamplePayload = {
      good: values.good,
      bad: values.bad,
      explanation: values.explanation || undefined,
    };
    addExample(payload);
  };

  const handleUpdateExample = (exampleId: string, payload: UpdateRuleExamplePayload) => {
    updateExample(exampleId, payload);
  };

  const handleRemoveExample = (exampleId: string) => {
    removeExample(exampleId);
  };

  return (
    <Card withBorder p="lg">
      <Stack gap="md">
        <Group justify="space-between" align="center">
          <Title order={4}>Examples ({examples.length})</Title>
          {!addFormOpened && (
            <Button
              leftSection={<IconPlus size={16} />}
              variant="light"
              size="sm"
              onClick={openAddForm}
            >
              Add Example
            </Button>
          )}
        </Group>

        <Collapse in={addFormOpened}>
          <Card withBorder p="md" bg="gray.0">
            <Stack gap="sm">
              <Title order={5}>New Example</Title>
              <RuleExampleForm
                key={formKey}
                onSubmit={handleAddExample}
                onCancel={closeAddForm}
                isLoading={isAdding}
                submitLabel="Add Example"
              />
            </Stack>
          </Card>
        </Collapse>

        {examples.length === 0 && !addFormOpened && (
          <Alert icon={<IconInfoCircle size={16} />} color="gray">
            No examples defined yet. Add examples to demonstrate the rule in action.
          </Alert>
        )}

        <Stack gap="sm">
          {examples.map((example) => (
            <RuleExampleCard
              key={example.id}
              example={example}
              onUpdate={handleUpdateExample}
              onRemove={handleRemoveExample}
              isUpdating={isUpdating}
              isRemoving={isRemoving}
            />
          ))}
        </Stack>
      </Stack>
    </Card>
  );
}
