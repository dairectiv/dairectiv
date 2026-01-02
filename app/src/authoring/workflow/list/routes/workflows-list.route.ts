import { createRoute } from "@tanstack/react-router";
import { z } from "zod";
import { WorkflowsListPage } from "@/authoring/workflow/list";
import { rootRoute } from "@/router";

const searchSchema = z.object({
  page: z.number().min(1).optional().default(1),
  search: z.string().optional(),
  state: z.enum(["draft", "published", "archived"]).optional(),
  sortBy: z.enum(["name", "createdAt", "updatedAt"]).optional().default("updatedAt"),
  sortOrder: z.enum(["asc", "desc"]).optional().default("desc"),
});

export type WorkflowsListSearch = z.infer<typeof searchSchema>;

export const workflowsListRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/workflows",
  component: WorkflowsListPage,
  validateSearch: searchSchema,
});
