import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { publishWorkflow as publishWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UsePublishWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function usePublishWorkflow(workflowId: string, options?: UsePublishWorkflowOptions) {
  const mutation = useMutation({
    mutationFn: async () => {
      await publishWorkflowApi({ path: { id: workflowId }, throwOnError: true });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Publishing workflow",
        message: "Workflow published successfully",
        loadingMessage: "Publishing your workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Workflow published",
        message: "The workflow has been published and is now available to AI tools.",
      });

      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Workflow not found",
          message: "The workflow could not be found. It may have been deleted.",
        });
      } else if (status === 409) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot publish workflow",
          message: "The workflow is not in draft state and cannot be published.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error publishing workflow",
          message: "An unexpected error occurred. Please try again.",
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
