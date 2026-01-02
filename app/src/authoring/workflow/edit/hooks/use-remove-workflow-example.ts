import { notifications } from "@mantine/notifications";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { removeWorkflowExample as removeWorkflowExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseRemoveWorkflowExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
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
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      notifications.show({
        title: "Example removed",
        message: "The example has been removed from the workflow.",
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
      } else {
        notifications.show({
          title: "Error removing example",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
