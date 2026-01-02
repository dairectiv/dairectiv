import { Alert, Card, Center, Loader, Stack, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { IconAlertCircle } from "@tabler/icons-react";
import { useNavigate, useParams } from "@tanstack/react-router";
import { useWorkflowDetail } from "../../detail/hooks/use-workflow-detail";
import { EditWorkflowForm } from "../components/edit-workflow-form";
import { WorkflowExamplesManager } from "../components/workflow-examples-manager";
import { WorkflowStepsManager } from "../components/workflow-steps-manager";
import { useUpdateWorkflow } from "../hooks/use-update-workflow";

export function EditWorkflowPage() {
  const navigate = useNavigate();
  const { workflowId } = useParams({ from: "/authoring/workflows/$workflowId/edit" });
  const { workflow, isLoading, isError, error } = useWorkflowDetail(workflowId);
  const { updateWorkflow, isUpdating } = useUpdateWorkflow(workflowId);

  const handleCancel = () => {
    navigate({ to: "/authoring/workflows/$workflowId", params: { workflowId } });
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
        <Alert icon={<IconAlertCircle size={16} />} title="Error loading workflow" color="red">
          {error?.message ?? "An unexpected error occurred"}
        </Alert>
      </AppLayout>
    );
  }

  if (!workflow) {
    return (
      <AppLayout>
        <Alert icon={<IconAlertCircle size={16} />} title="Workflow not found" color="yellow">
          The requested workflow could not be found.
        </Alert>
      </AppLayout>
    );
  }

  // Only allow editing draft workflows
  if (workflow.state !== "draft") {
    return (
      <AppLayout>
        <Alert icon={<IconAlertCircle size={16} />} title="Cannot edit workflow" color="orange">
          Only draft workflows can be edited. This workflow is currently in &quot;{workflow.state}
          &quot; state.
        </Alert>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <Stack gap="lg" py="md">
        <Title order={2}>Edit Workflow</Title>

        <Card withBorder p="lg" maw={600}>
          <EditWorkflowForm
            initialValues={{
              name: workflow.name,
              description: workflow.description,
            }}
            onSubmit={updateWorkflow}
            isLoading={isUpdating}
            onCancel={handleCancel}
          />
        </Card>

        <WorkflowStepsManager workflowId={workflowId} steps={workflow.steps} />

        <WorkflowExamplesManager workflowId={workflowId} examples={workflow.examples} />
      </Stack>
    </AppLayout>
  );
}
