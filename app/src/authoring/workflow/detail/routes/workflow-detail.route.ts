import { createRoute } from "@tanstack/react-router";
import { rootRoute } from "@/router";
import { WorkflowDetailPage } from "../pages/workflow-detail.page";

export const workflowDetailRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/workflows/$workflowId",
  component: WorkflowDetailPage,
});
