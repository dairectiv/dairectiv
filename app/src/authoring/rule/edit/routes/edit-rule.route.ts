import { createRoute } from "@tanstack/react-router";
import { rootRoute } from "@/router";
import { EditRulePage } from "../pages/edit-rule.page";

export const editRuleRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/authoring/rules/$ruleId/edit",
  component: EditRulePage,
});
