import { Card, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { useNavigate } from "@tanstack/react-router";
import { CreateRuleForm } from "../components/create-rule-form";
import { useDraftRule } from "../hooks/use-draft-rule";

export function CreateRulePage() {
  const navigate = useNavigate();
  const { draftRule, isLoading } = useDraftRule();

  const handleCancel = () => {
    navigate({ to: "/authoring/rules" });
  };

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Title order={2}>Create Rule</Title>

        <Card withBorder p="lg" maw={600}>
          <CreateRuleForm onSubmit={draftRule} isLoading={isLoading} onCancel={handleCancel} />
        </Card>
      </Stack>
    </AppLayout>
  );
}
