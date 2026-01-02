import { AppLayout } from "@shared/ui/layout";
import { RulesList } from "@/authoring/ui/rules";
import { useRulesList } from "./use-rules-list";

export function RulesListPage() {
  const { rules, pagination, isLoading, isError, error, onPageChange } = useRulesList();

  return (
    <AppLayout>
      <RulesList
        rules={rules}
        pagination={pagination}
        isLoading={isLoading}
        isError={isError}
        error={error}
        onPageChange={onPageChange}
      />
    </AppLayout>
  );
}
