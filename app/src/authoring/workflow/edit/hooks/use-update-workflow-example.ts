import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateWorkflowExample as updateWorkflowExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateWorkflowExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseUpdateWorkflowExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useUpdateWorkflowExample(
  workflowId: string,
  options?: UseUpdateWorkflowExampleOptions,
) {
  const mutation = useMutation({
    mutationFn: async ({
      exampleId,
      payload,
    }: {
      exampleId: string;
      payload: UpdateWorkflowExamplePayload;
    }) => {
      const { data } = await updateWorkflowExampleApi({
        path: { id: workflowId, exampleId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Updating example",
        message: "Example updated successfully",
        loadingMessage: "Saving example changes...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Example updated",
        message: "The example has been updated successfully.",
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
      } else if (status === 400) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot update example",
          message: "The example does not exist or the workflow is archived.",
        });
      } else if (status === 422) {
        updateToError(context?.notificationId ?? "", {
          title: "Validation error",
          message: "Please check the example fields and try again.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error updating example",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const updateExample = (exampleId: string, payload: UpdateWorkflowExamplePayload) => {
    mutation.mutate({ exampleId, payload });
  };

  return {
    updateExample,
    isUpdating: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
