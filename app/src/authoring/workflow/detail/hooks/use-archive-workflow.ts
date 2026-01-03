import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { archiveWorkflow as archiveWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseArchiveWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useArchiveWorkflow(workflowId: string, options?: UseArchiveWorkflowOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await archiveWorkflowApi({ path: { id: workflowId }, throwOnError: true });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Archiving workflow",
        message: "Workflow archived successfully",
        loadingMessage: "Archiving your workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Workflow archived",
        message: "The workflow has been archived successfully.",
      });

      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      // Navigate back to workflows list
      navigate({ to: "/authoring/workflows" });

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
          title: "Cannot archive workflow",
          message: "The workflow is already archived.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error archiving workflow",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const archiveWorkflow = () => {
    mutation.mutate();
  };

  return {
    archiveWorkflow,
    isArchiving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
