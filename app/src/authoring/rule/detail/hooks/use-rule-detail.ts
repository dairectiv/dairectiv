import { getRuleOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { useQuery } from "@tanstack/react-query";

export function useRuleDetail(ruleId: string) {
  const query = useQuery({
    ...getRuleOptions({ path: { id: ruleId } }),
  });

  return {
    rule: query.data,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
  };
}
