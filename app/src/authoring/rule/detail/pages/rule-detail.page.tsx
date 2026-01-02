import { AppLayout } from "@shared/ui/layout";
import { useParams } from "@tanstack/react-router";
import { RuleDetail } from "../components/rule-detail";
import { useRuleDetail } from "../hooks/use-rule-detail";

export function RuleDetailPage() {
  const { ruleId } = useParams({ from: "/authoring/rules/$ruleId" });
  const { rule, isLoading, isError, error } = useRuleDetail(ruleId);

  return (
    <AppLayout>
      <RuleDetail rule={rule} isLoading={isLoading} isError={isError} error={error} />
    </AppLayout>
  );
}
