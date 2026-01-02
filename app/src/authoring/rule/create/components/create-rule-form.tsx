import { Button, Group, Stack, Textarea, TextInput } from "@mantine/core";
import { useForm } from "@mantine/form";
import type { DraftRulePayload } from "@shared/infrastructure/api/generated/types.gen";

export interface CreateRuleFormValues {
  name: string;
  description: string;
}

export interface CreateRuleFormProps {
  onSubmit: (values: DraftRulePayload) => void;
  isLoading?: boolean;
  onCancel?: () => void;
}

export function CreateRuleForm({ onSubmit, isLoading, onCancel }: CreateRuleFormProps) {
  const form = useForm<CreateRuleFormValues>({
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

  const handleSubmit = (values: CreateRuleFormValues) => {
    onSubmit(values);
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
          description="Explain what this rule is about and when it should be applied"
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
            Create Rule
          </Button>
        </Group>
      </Stack>
    </form>
  );
}
