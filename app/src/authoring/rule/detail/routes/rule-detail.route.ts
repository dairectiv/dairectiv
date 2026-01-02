import { createRoute } from "@tanstack/react-router";
import { rootRoute } from "@/router";
import { RuleDetailPage } from "../pages/rule-detail.page";

export const ruleDetailRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/rules/$ruleId",
  component: RuleDetailPage,
});
