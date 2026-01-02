import { createRoute } from "@tanstack/react-router";
import { z } from "zod";
import { RulesListPage } from "@/authoring/rule/list";
import { rootRoute } from "@/router";

const searchSchema = z.object({
  page: z.number().min(1).optional().default(1),
  search: z.string().optional(),
  state: z.enum(["draft", "published", "archived"]).optional(),
  sortBy: z.enum(["name", "createdAt", "updatedAt"]).optional(),
  sortOrder: z.enum(["asc", "desc"]).optional(),
});

export type RulesListSearch = z.infer<typeof searchSchema>;

export const rulesListRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/rules",
  component: RulesListPage,
  validateSearch: searchSchema,
});
