import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { deleteWorkflow as deleteWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDeleteWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useDeleteWorkflow(workflowId: string, options?: UseDeleteWorkflowOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await deleteWorkflowApi({ path: { id: workflowId }, throwOnError: true });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Deleting workflow",
        message: "Workflow deleted successfully",
        loadingMessage: "Deleting your workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Workflow deleted",
        message: "The workflow has been permanently deleted.",
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
          message: "The workflow could not be found. It may have already been deleted.",
        });
      } else if (status === 409) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot delete workflow",
          message: "The workflow is already deleted.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error deleting workflow",
          message: "An unexpected error occurred. Please try again.",
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
