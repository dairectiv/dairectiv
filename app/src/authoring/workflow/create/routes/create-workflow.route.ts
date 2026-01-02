import { createRoute } from "@tanstack/react-router";
import { rootRoute } from "@/router";
import { CreateWorkflowPage } from "../pages/create-workflow.page";

export const createWorkflowRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/workflows/new",
  component: CreateWorkflowPage,
});
