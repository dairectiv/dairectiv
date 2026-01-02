import { Button, Group, Stack, Textarea } from "@mantine/core";
import { useForm } from "@mantine/form";

export interface WorkflowStepFormValues {
  content: string;
}

export interface WorkflowStepFormProps {
  initialValues?: Partial<WorkflowStepFormValues>;
  onSubmit: (values: WorkflowStepFormValues) => void;
  onCancel: () => void;
  isLoading?: boolean;
  submitLabel?: string;
}

export function WorkflowStepForm({
  initialValues,
  onSubmit,
  onCancel,
  isLoading = false,
  submitLabel = "Save",
}: WorkflowStepFormProps) {
  const form = useForm<WorkflowStepFormValues>({
    mode: "uncontrolled",
    initialValues: {
      content: initialValues?.content ?? "",
    },
    validate: {
      content: (value) => {
        if (!value || value.trim().length === 0) {
          return "Step content is required";
        }
        return null;
      },
    },
  });

  const handleSubmit = (values: WorkflowStepFormValues) => {
    onSubmit(values);
  };

  return (
    <form onSubmit={form.onSubmit(handleSubmit)}>
      <Stack gap="md">
        <Textarea
          label="Step content"
          placeholder="Describe the step instructions"
          description="Enter the content/instructions for this step"
          required
          minRows={3}
          autosize
          disabled={isLoading}
          key={form.key("content")}
          {...form.getInputProps("content")}
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
