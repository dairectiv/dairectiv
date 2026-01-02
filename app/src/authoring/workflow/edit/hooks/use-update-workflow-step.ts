import { notifications } from "@mantine/notifications";
import {
  type UpdateWorkflowStepPayload,
  updateWorkflowStep as updateWorkflowStepApi,
} from "@shared/infrastructure/api/generated";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";

export function useUpdateWorkflowStep(workflowId: string) {
  const mutation = useMutation({
    mutationFn: async ({
      stepId,
      payload,
    }: {
      stepId: string;
      payload: UpdateWorkflowStepPayload;
    }) => {
      const response = await updateWorkflowStepApi({
        path: { id: workflowId, stepId },
        body: payload,
      });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });
      notifications.show({
        title: "Step updated",
        message: "The step has been updated successfully.",
        color: "green",
      });
    },
    onError: (error: Error & { response?: { status: number } }) => {
      const status = error.response?.status;
      let message = "An error occurred while updating the step.";

      if (status === 404) {
        message = "The workflow does not exist.";
      } else if (status === 400) {
        message = "The step does not exist or the workflow is archived.";
      }

      notifications.show({
        title: "Error",
        message,
        color: "red",
      });
    },
  });

  return {
    updateStep: (stepId: string, payload: UpdateWorkflowStepPayload) =>
      mutation.mutate({ stepId, payload }),
    isUpdating: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
