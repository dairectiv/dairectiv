import { listWorkflowsOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { useQuery } from "@tanstack/react-query";
import { useNavigate, useSearch } from "@tanstack/react-router";

export type WorkflowsListStateFilter = "draft" | "published" | "archived";

export interface WorkflowsListFilters {
  page: number;
  search?: string;
  state?: WorkflowsListStateFilter;
  sortBy?: "name" | "createdAt" | "updatedAt";
  sortOrder?: "asc" | "desc";
}

export function useWorkflowsList() {
  const navigate = useNavigate();
  const filters = useSearch({ from: "/authoring/workflows" }) as WorkflowsListFilters;

  const { data, isLoading, isError, error } = useQuery(
    listWorkflowsOptions({
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

  const updateFilters = (newFilters: Partial<WorkflowsListFilters>) => {
    navigate({
      to: "/authoring/workflows",
      search: { ...filters, ...newFilters },
    });
  };

  const setPage = (page: number) => updateFilters({ page });
  const setSearch = (search: string) => updateFilters({ search: search || undefined, page: 1 });
  const setState = (state: WorkflowsListStateFilter | undefined) =>
    updateFilters({ state, page: 1 });
  const setSort = (
    sortBy: WorkflowsListFilters["sortBy"],
    sortOrder: WorkflowsListFilters["sortOrder"],
  ) => updateFilters({ sortBy, sortOrder });

  return {
    workflows: data?.items ?? [],
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
