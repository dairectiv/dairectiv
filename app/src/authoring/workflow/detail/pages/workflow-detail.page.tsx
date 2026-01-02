import { AppLayout } from "@shared/ui/layout";
import { useParams } from "@tanstack/react-router";
import { WorkflowDetail } from "../components/workflow-detail";
import { useArchiveWorkflow } from "../hooks/use-archive-workflow";
import { useWorkflowDetail } from "../hooks/use-workflow-detail";

export function WorkflowDetailPage() {
  const { workflowId } = useParams({ from: "/authoring/workflows/$workflowId" });
  const { workflow, isLoading, isError, error } = useWorkflowDetail(workflowId);
  const { archiveWorkflow, isArchiving } = useArchiveWorkflow(workflowId);

  return (
    <AppLayout>
      <WorkflowDetail
        workflow={workflow}
        isLoading={isLoading}
        isError={isError}
        error={error}
        onArchive={archiveWorkflow}
        isArchiving={isArchiving}
      />
    </AppLayout>
  );
}
