import { notifications } from "@mantine/notifications";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateWorkflowExample as updateWorkflowExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateWorkflowExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseUpdateWorkflowExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
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
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      notifications.show({
        title: "Example updated",
        message: "The example has been updated successfully.",
        color: "green",
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Not found",
          message: "The workflow or example does not exist.",
          color: "red",
        });
      } else if (status === 400) {
        notifications.show({
          title: "Cannot update example",
          message: "The example does not exist or the workflow is archived.",
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
          title: "Error updating example",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
