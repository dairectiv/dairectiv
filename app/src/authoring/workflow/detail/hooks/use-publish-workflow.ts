import { notifications } from "@mantine/notifications";
import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { publishWorkflow as publishWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UsePublishWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function usePublishWorkflow(workflowId: string, options?: UsePublishWorkflowOptions) {
  const mutation = useMutation({
    mutationFn: async () => {
      await publishWorkflowApi({ path: { id: workflowId }, throwOnError: true });
    },
    onSuccess: () => {
      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      notifications.show({
        title: "Workflow published",
        message: "The workflow has been published and is now available to AI tools.",
        color: "green",
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Workflow not found",
          message: "The workflow could not be found. It may have been deleted.",
          color: "red",
        });
      } else if (status === 409) {
        notifications.show({
          title: "Cannot publish workflow",
          message: "The workflow is not in draft state and cannot be published.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error publishing workflow",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const publishWorkflow = () => {
    mutation.mutate();
  };

  return {
    publishWorkflow,
    isPublishing: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
