import { Card, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { useNavigate } from "@tanstack/react-router";
import { CreateWorkflowForm } from "../components/create-workflow-form";
import { useDraftWorkflow } from "../hooks/use-draft-workflow";

export function CreateWorkflowPage() {
  const navigate = useNavigate();
  const { draftWorkflow, isLoading } = useDraftWorkflow();

  const handleCancel = () => {
    navigate({ to: "/authoring/workflows" });
  };

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Title order={2}>Create Workflow</Title>

        <Card withBorder p="lg" maw={600}>
          <CreateWorkflowForm
            onSubmit={draftWorkflow}
            isLoading={isLoading}
            onCancel={handleCancel}
          />
        </Card>
      </Stack>
    </AppLayout>
  );
}
