import { Button, Group, Stack, Textarea, TextInput } from "@mantine/core";
import { useForm } from "@mantine/form";
import type { UpdateRulePayload } from "@shared/infrastructure/api/generated/types.gen";

export interface EditRuleFormValues {
  name: string;
  description: string;
  content: string;
}

export interface EditRuleFormProps {
  initialValues: EditRuleFormValues;
  onSubmit: (values: UpdateRulePayload) => void;
  isLoading?: boolean;
  onCancel?: () => void;
}

export function EditRuleForm({ initialValues, onSubmit, isLoading, onCancel }: EditRuleFormProps) {
  const form = useForm<EditRuleFormValues>({
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

  const handleSubmit = (values: EditRuleFormValues) => {
    // Only include content if it has a value, otherwise send undefined
    const payload: UpdateRulePayload = {
      name: values.name,
      description: values.description,
      content: values.content || undefined,
    };
    onSubmit(payload);
  };

  return (
    <form onSubmit={form.onSubmit(handleSubmit)}>
      <Stack gap="md">
        <TextInput
          label="Name"
          placeholder="Enter rule name"
          description="A clear, descriptive name for this rule"
          required
          disabled={isLoading}
          key={form.key("name")}
          {...form.getInputProps("name")}
        />

        <Textarea
          label="Description"
          placeholder="Enter rule description"
          description="Explain what this rule is about and when it should be used"
          required
          minRows={3}
          autosize
          disabled={isLoading}
          key={form.key("description")}
          {...form.getInputProps("description")}
        />

        <Textarea
          label="Content"
          placeholder="Enter rule content (optional)"
          description="The actual content or body of the rule"
          minRows={5}
          autosize
          disabled={isLoading}
          key={form.key("content")}
          {...form.getInputProps("content")}
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
