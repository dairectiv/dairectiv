import { Button, Group, Stack, Textarea, TextInput } from "@mantine/core";
import { useForm } from "@mantine/form";
import type { DraftWorkflowPayload } from "@shared/infrastructure/api/generated/types.gen";

export interface CreateWorkflowFormValues {
  name: string;
  description: string;
}

export interface CreateWorkflowFormProps {
  onSubmit: (values: DraftWorkflowPayload) => void;
  isLoading?: boolean;
  onCancel?: () => void;
}

export function CreateWorkflowForm({ onSubmit, isLoading, onCancel }: CreateWorkflowFormProps) {
  const form = useForm<CreateWorkflowFormValues>({
    mode: "uncontrolled",
    initialValues: {
      name: "",
      description: "",
    },
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

  const handleSubmit = (values: CreateWorkflowFormValues) => {
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
            Create Workflow
          </Button>
        </Group>
      </Stack>
    </form>
  );
}
