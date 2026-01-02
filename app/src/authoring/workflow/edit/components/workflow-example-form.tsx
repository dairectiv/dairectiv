import { Button, Group, Stack, Textarea } from "@mantine/core";
import { useForm } from "@mantine/form";

export interface WorkflowExampleFormValues {
  scenario: string;
  input: string;
  output: string;
  explanation: string;
}

export interface WorkflowExampleFormProps {
  initialValues?: Partial<WorkflowExampleFormValues>;
  onSubmit: (values: WorkflowExampleFormValues) => void;
  onCancel: () => void;
  isLoading?: boolean;
  submitLabel?: string;
}

export function WorkflowExampleForm({
  initialValues,
  onSubmit,
  onCancel,
  isLoading = false,
  submitLabel = "Save",
}: WorkflowExampleFormProps) {
  const form = useForm<WorkflowExampleFormValues>({
    mode: "uncontrolled",
    initialValues: {
      scenario: initialValues?.scenario ?? "",
      input: initialValues?.input ?? "",
      output: initialValues?.output ?? "",
      explanation: initialValues?.explanation ?? "",
    },
    validate: {
      scenario: (value) => {
        if (!value || value.trim().length === 0) {
          return "Scenario is required";
        }
        return null;
      },
      input: (value) => {
        if (!value || value.trim().length === 0) {
          return "Input is required";
        }
        return null;
      },
      output: (value) => {
        if (!value || value.trim().length === 0) {
          return "Output is required";
        }
        return null;
      },
    },
  });

  const handleSubmit = (values: WorkflowExampleFormValues) => {
    onSubmit(values);
  };

  return (
    <form onSubmit={form.onSubmit(handleSubmit)}>
      <Stack gap="md">
        <Textarea
          label="Scenario"
          placeholder="Describe the use case scenario"
          description="When should this workflow be used?"
          required
          minRows={2}
          autosize
          disabled={isLoading}
          key={form.key("scenario")}
          {...form.getInputProps("scenario")}
        />

        <Textarea
          label="Input"
          placeholder="Example input for this scenario"
          description="What input triggers this workflow?"
          required
          minRows={2}
          autosize
          disabled={isLoading}
          key={form.key("input")}
          {...form.getInputProps("input")}
        />

        <Textarea
          label="Output"
          placeholder="Expected output for this scenario"
          description="What result should the workflow produce?"
          required
          minRows={2}
          autosize
          disabled={isLoading}
          key={form.key("output")}
          {...form.getInputProps("output")}
        />

        <Textarea
          label="Explanation"
          placeholder="Optional explanation"
          description="Additional context about this example"
          minRows={2}
          autosize
          disabled={isLoading}
          key={form.key("explanation")}
          {...form.getInputProps("explanation")}
        />

        <Group justify="flex-end" gap="sm">
          <Button variant="subtle" onClick={onCancel} disabled={isLoading}>
            Cancel
          </Button>
          <Button type="submit" loading={isLoading}>
            {submitLabel}
          </Button>
        </Group>
      </Stack>
    </form>
  );
}
