import { listRulesOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { useQuery } from "@tanstack/react-query";
import { useNavigate, useSearch } from "@tanstack/react-router";

export function useRulesList() {
  const navigate = useNavigate();
  const { page } = useSearch({ from: "/authoring/rules" });

  const { data, isLoading, isError, error } = useQuery(
    listRulesOptions({
      query: { page, limit: 10 },
    }),
  );

  const handlePageChange = (newPage: number) => {
    navigate({
      to: "/authoring/rules",
      search: { page: newPage },
    });
  };

  return {
    rules: data?.items ?? [],
    pagination: data?.pagination,
    isLoading,
    isError,
    error,
    onPageChange: handlePageChange,
  };
}
