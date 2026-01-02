import { getWorkflowOptions } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { useQuery } from "@tanstack/react-query";

export function useWorkflowDetail(workflowId: string) {
  const query = useQuery({
    ...getWorkflowOptions({ path: { id: workflowId } }),
  });

  return {
    workflow: query.data,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
  };
}
