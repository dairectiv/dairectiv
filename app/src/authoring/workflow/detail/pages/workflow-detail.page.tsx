import { AppLayout } from "@shared/ui/layout";
import { useParams } from "@tanstack/react-router";
import { WorkflowDetail } from "../components/workflow-detail";
import { useArchiveWorkflow } from "../hooks/use-archive-workflow";
import { useDeleteWorkflow } from "../hooks/use-delete-workflow";
import { usePublishWorkflow } from "../hooks/use-publish-workflow";
import { useWorkflowDetail } from "../hooks/use-workflow-detail";

export function WorkflowDetailPage() {
  const { workflowId } = useParams({ from: "/authoring/workflows/$workflowId" });
  const { workflow, isLoading, isError, error } = useWorkflowDetail(workflowId);
  const { publishWorkflow, isPublishing } = usePublishWorkflow(workflowId);
  const { archiveWorkflow, isArchiving } = useArchiveWorkflow(workflowId);
  const { deleteWorkflow, isDeleting } = useDeleteWorkflow(workflowId);

  return (
    <AppLayout>
      <WorkflowDetail
        workflow={workflow}
        isLoading={isLoading}
        isError={isError}
        error={error}
        onPublish={publishWorkflow}
        isPublishing={isPublishing}
        onArchive={archiveWorkflow}
        isArchiving={isArchiving}
        onDelete={deleteWorkflow}
        isDeleting={isDeleting}
      />
    </AppLayout>
  );
}
