import { createRoute } from "@tanstack/react-router";
import { rootRoute } from "@/router";
import { EditWorkflowPage } from "../pages/edit-workflow.page";

export const editWorkflowRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/workflows/$workflowId/edit",
  component: EditWorkflowPage,
});
