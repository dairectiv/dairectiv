import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { removeWorkflowExample as removeWorkflowExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseRemoveWorkflowExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useRemoveWorkflowExample(
  workflowId: string,
  options?: UseRemoveWorkflowExampleOptions,
) {
  const mutation = useMutation({
    mutationFn: async (exampleId: string) => {
      await removeWorkflowExampleApi({
        path: { id: workflowId, exampleId },
        throwOnError: true,
      });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Removing example",
        message: "Example removed successfully",
        loadingMessage: "Removing example from workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Example removed",
        message: "The example has been removed from the workflow.",
      });

      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Not found",
          message: "The workflow or example does not exist.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error removing example",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const removeExample = (exampleId: string) => {
    mutation.mutate(exampleId);
  };

  return {
    removeExample,
    isRemoving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
