import { Button, Group, Stack, Textarea } from "@mantine/core";
import { useForm } from "@mantine/form";

export interface RuleExampleFormValues {
  good: string;
  bad: string;
  explanation: string;
}

export interface RuleExampleFormProps {
  initialValues?: Partial<RuleExampleFormValues>;
  onSubmit: (values: RuleExampleFormValues) => void;
  onCancel: () => void;
  isLoading?: boolean;
  submitLabel?: string;
}

export function RuleExampleForm({
  initialValues,
  onSubmit,
  onCancel,
  isLoading = false,
  submitLabel = "Save",
}: RuleExampleFormProps) {
  const form = useForm<RuleExampleFormValues>({
    mode: "uncontrolled",
    initialValues: {
      good: initialValues?.good ?? "",
      bad: initialValues?.bad ?? "",
      explanation: initialValues?.explanation ?? "",
    },
    validate: {
      good: (value) => {
        if (!value || value.trim().length === 0) {
          return "Good example is required";
        }
        return null;
      },
      bad: (value) => {
        if (!value || value.trim().length === 0) {
          return "Bad example is required";
        }
        return null;
      },
    },
  });

  const handleSubmit = (values: RuleExampleFormValues) => {
    onSubmit(values);
  };

  return (
    <form onSubmit={form.onSubmit(handleSubmit)}>
      <Stack gap="md">
        <Textarea
          label="Good example"
          placeholder="Example of correct usage"
          description="Show how the rule should be followed correctly"
          required
          minRows={2}
          autosize
          disabled={isLoading}
          key={form.key("good")}
          {...form.getInputProps("good")}
        />

        <Textarea
          label="Bad example"
          placeholder="Example of incorrect usage"
          description="Show how the rule is commonly violated"
          required
          minRows={2}
          autosize
          disabled={isLoading}
          key={form.key("bad")}
          {...form.getInputProps("bad")}
        />

        <Textarea
          label="Explanation"
          placeholder="Optional explanation (why the good example is better)"
          description="Help users understand why the good example is preferred"
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
