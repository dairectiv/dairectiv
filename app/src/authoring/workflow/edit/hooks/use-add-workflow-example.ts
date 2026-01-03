import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { addWorkflowExample as addWorkflowExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { AddWorkflowExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseAddWorkflowExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useAddWorkflowExample(workflowId: string, options?: UseAddWorkflowExampleOptions) {
  const mutation = useMutation({
    mutationFn: async (payload: AddWorkflowExamplePayload) => {
      const { data } = await addWorkflowExampleApi({
        path: { id: workflowId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Adding example",
        message: "Example added successfully",
        loadingMessage: "Adding example to workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Example added",
        message: "The example has been added to the workflow.",
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
          title: "Workflow not found",
          message: "The workflow you are trying to add an example to does not exist.",
        });
      } else if (status === 400) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot add example",
          message: "The workflow is archived and cannot be modified.",
        });
      } else if (status === 422) {
        updateToError(context?.notificationId ?? "", {
          title: "Validation error",
          message: "Please check the example fields and try again.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error adding example",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const addExample = (payload: AddWorkflowExamplePayload) => {
    mutation.mutate(payload);
  };

  return {
    addExample,
    isAdding: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
