import { Alert, Card, Center, Loader, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { IconAlertCircle } from "@tabler/icons-react";
import { useNavigate, useParams } from "@tanstack/react-router";
import { useRuleDetail } from "../../detail/hooks/use-rule-detail";
import { EditRuleForm } from "../components/edit-rule-form";
import { RuleExamplesManager } from "../components/rule-examples-manager";
import { useUpdateRule } from "../hooks/use-update-rule";

export function EditRulePage() {
  const navigate = useNavigate();
  const { ruleId } = useParams({ from: "/authoring/rules/$ruleId/edit" });
  const { rule, isLoading, isError, error } = useRuleDetail(ruleId);
  const { updateRule, isUpdating } = useUpdateRule(ruleId);

  const handleCancel = () => {
    navigate({ to: "/authoring/rules/$ruleId", params: { ruleId } });
  };

  if (isLoading) {
    return (
      <AppLayout>
        <Center py="xl">
          <Loader size="lg" />
        </Center>
      </AppLayout>
    );
  }

  if (isError) {
    return (
      <AppLayout>
        <Alert icon={<IconAlertCircle size={16} />} title="Error loading rule" color="red">
          {error?.message ?? "An unexpected error occurred"}
        </Alert>
      </AppLayout>
    );
  }

  if (!rule) {
    return (
      <AppLayout>
        <Alert icon={<IconAlertCircle size={16} />} title="Rule not found" color="yellow">
          The requested rule could not be found.
        </Alert>
      </AppLayout>
    );
  }

  // Only allow editing draft rules
  if (rule.state !== "draft") {
    return (
      <AppLayout>
        <Alert icon={<IconAlertCircle size={16} />} title="Cannot edit rule" color="orange">
          Only draft rules can be edited. This rule is currently in &quot;{rule.state}&quot; state.
        </Alert>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Title order={2}>Edit Rule</Title>

        <Card withBorder p="lg" maw={800}>
          <EditRuleForm
            initialValues={{
              name: rule.name,
              description: rule.description,
              content: rule.content ?? "",
            }}
            onSubmit={updateRule}
            isLoading={isUpdating}
            onCancel={handleCancel}
          />
        </Card>

        <Stack gap="sm" maw={800}>
          <RuleExamplesManager ruleId={rule.id} examples={rule.examples} />
        </Stack>
      </Stack>
    </AppLayout>
  );
}
