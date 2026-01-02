import { AppLayout } from "@shared/ui/layout";
import { useParams } from "@tanstack/react-router";
import { RuleDetail } from "../components/rule-detail";
import { useArchiveRule } from "../hooks/use-archive-rule";
import { useRuleDetail } from "../hooks/use-rule-detail";

export function RuleDetailPage() {
  const { ruleId } = useParams({ from: "/authoring/rules/$ruleId" });
  const { rule, isLoading, isError, error } = useRuleDetail(ruleId);
  const { archiveRule, isArchiving } = useArchiveRule(ruleId);

  return (
    <AppLayout>
      <RuleDetail
        rule={rule}
        isLoading={isLoading}
        isError={isError}
        error={error}
        onArchive={archiveRule}
        isArchiving={isArchiving}
      />
    </AppLayout>
  );
}
