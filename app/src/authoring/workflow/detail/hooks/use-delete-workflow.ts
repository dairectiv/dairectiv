import { notifications } from "@mantine/notifications";
import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { deleteWorkflow as deleteWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDeleteWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useDeleteWorkflow(workflowId: string, options?: UseDeleteWorkflowOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await deleteWorkflowApi({ path: { id: workflowId }, throwOnError: true });
    },
    onSuccess: () => {
      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      notifications.show({
        title: "Workflow deleted",
        message: "The workflow has been permanently deleted.",
        color: "green",
      });

      // Navigate back to workflows list
      navigate({ to: "/authoring/workflows" });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Workflow not found",
          message: "The workflow could not be found. It may have already been deleted.",
          color: "red",
        });
      } else if (status === 409) {
        notifications.show({
          title: "Cannot delete workflow",
          message: "The workflow is already deleted.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error deleting workflow",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const deleteWorkflow = () => {
    mutation.mutate();
  };

  return {
    deleteWorkflow,
    isDeleting: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
