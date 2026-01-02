import { notifications } from "@mantine/notifications";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { moveWorkflowStep as moveWorkflowStepApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { MoveWorkflowStepPayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseMoveWorkflowStepOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useMoveWorkflowStep(workflowId: string, options?: UseMoveWorkflowStepOptions) {
  const mutation = useMutation({
    mutationFn: async ({
      stepId,
      payload,
    }: {
      stepId: string;
      payload: MoveWorkflowStepPayload;
    }) => {
      const { data } = await moveWorkflowStepApi({
        path: { id: workflowId, stepId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Not found",
          message: "The workflow or step does not exist.",
          color: "red",
        });
      } else if (status === 400) {
        notifications.show({
          title: "Cannot move step",
          message: "The workflow is archived or the step configuration is invalid.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error moving step",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const moveStep = (stepId: string, afterStepId: string | null) => {
    mutation.mutate({ stepId, payload: { afterStepId } });
  };

  return {
    moveStep,
    isMoving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
