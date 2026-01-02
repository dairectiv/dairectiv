import { Button, Group, Stack, Textarea, TextInput } from "@mantine/core";
import { useForm } from "@mantine/form";
import type { UpdateWorkflowPayload } from "@shared/infrastructure/api/generated/types.gen";

export interface EditWorkflowFormValues {
  name: string;
  description: string;
}

export interface EditWorkflowFormProps {
  initialValues: EditWorkflowFormValues;
  onSubmit: (values: UpdateWorkflowPayload) => void;
  isLoading?: boolean;
  onCancel?: () => void;
}

export function EditWorkflowForm({
  initialValues,
  onSubmit,
  isLoading,
  onCancel,
}: EditWorkflowFormProps) {
  const form = useForm<EditWorkflowFormValues>({
    mode: "uncontrolled",
    initialValues,
    validate: {
      name: (value) => {
        if (!value || value.trim().length === 0) {
          return "Name is required";
        }
        if (value.length > 255) {
          return "Name must be at most 255 characters";
        }
        return null;
      },
      description: (value) => {
        if (!value || value.trim().length === 0) {
          return "Description is required";
        }
        if (value.length > 1000) {
          return "Description must be at most 1000 characters";
        }
        return null;
      },
    },
  });

  const handleSubmit = (values: EditWorkflowFormValues) => {
    onSubmit(values);
  };

  return (
    <form onSubmit={form.onSubmit(handleSubmit)}>
      <Stack gap="md">
        <TextInput
          label="Name"
          placeholder="Enter workflow name"
          description="A clear, descriptive name for this workflow"
          required
          disabled={isLoading}
          key={form.key("name")}
          {...form.getInputProps("name")}
        />

        <Textarea
          label="Description"
          placeholder="Enter workflow description"
          description="Explain what this workflow is about and when it should be used"
          required
          minRows={3}
          autosize
          disabled={isLoading}
          key={form.key("description")}
          {...form.getInputProps("description")}
        />

        <Group justify="flex-end" mt="md">
          {onCancel && (
            <Button variant="subtle" onClick={onCancel} disabled={isLoading}>
              Cancel
            </Button>
          )}
          <Button type="submit" loading={isLoading}>
            Save Changes
          </Button>
        </Group>
      </Stack>
    </form>
  );
}
