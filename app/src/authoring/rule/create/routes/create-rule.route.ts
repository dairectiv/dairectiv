import { createRoute } from "@tanstack/react-router";
import { rootRoute } from "@/router";
import { CreateRulePage } from "../pages/create-rule.page";

export const createRuleRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/rules/new",
  component: CreateRulePage,
});
