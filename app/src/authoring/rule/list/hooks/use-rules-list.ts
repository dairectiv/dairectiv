import { listRulesOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { useQuery } from "@tanstack/react-query";
import { useNavigate, useSearch } from "@tanstack/react-router";

export type RulesListStateFilter = "draft" | "published" | "archived";

export interface RulesListFilters {
  page: number;
  search?: string;
  state?: RulesListStateFilter;
  sortBy?: "name" | "createdAt" | "updatedAt";
  sortOrder?: "asc" | "desc";
}

export function useRulesList() {
  const navigate = useNavigate();
  const filters = useSearch({ from: "/authoring/rules" }) as RulesListFilters;

  const { data, isLoading, isError, error } = useQuery(
    listRulesOptions({
      query: {
        page: filters.page,
        limit: 10,
        search: filters.search,
        state: filters.state,
        sortBy: filters.sortBy,
        sortOrder: filters.sortOrder,
      },
    }),
  );

  const updateFilters = (newFilters: Partial<RulesListFilters>) => {
    navigate({
      to: "/authoring/rules",
      search: { ...filters, ...newFilters },
    });
  };

  const setPage = (page: number) => updateFilters({ page });
  const setSearch = (search: string) => updateFilters({ search: search || undefined, page: 1 });
  const setState = (state: RulesListStateFilter | undefined) => updateFilters({ state, page: 1 });
  const setSort = (sortBy: RulesListFilters["sortBy"], sortOrder: RulesListFilters["sortOrder"]) =>
    updateFilters({ sortBy, sortOrder });

  return {
    rules: data?.items ?? [],
    pagination: data?.pagination,
    filters,
    isLoading,
    isError,
    error,
    setPage,
    setSearch,
    setState,
    setSort,
  };
}
