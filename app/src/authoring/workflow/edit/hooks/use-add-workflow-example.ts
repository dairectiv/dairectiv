import { notifications } from "@mantine/notifications";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { addWorkflowExample as addWorkflowExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { AddWorkflowExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseAddWorkflowExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
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
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      notifications.show({
        title: "Example added",
        message: "The example has been added to the workflow.",
        color: "green",
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Workflow not found",
          message: "The workflow you are trying to add an example to does not exist.",
          color: "red",
        });
      } else if (status === 400) {
        notifications.show({
          title: "Cannot add example",
          message: "The workflow is archived and cannot be modified.",
          color: "red",
        });
      } else if (status === 422) {
        notifications.show({
          title: "Validation error",
          message: "Please check the example fields and try again.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error adding example",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
