import { notifications } from "@mantine/notifications";
import {
  type AddWorkflowStepPayload,
  addWorkflowStep as addWorkflowStepApi,
} from "@shared/infrastructure/api/generated";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";

export interface UseAddWorkflowStepOptions {
  onSuccess?: () => void;
}

export function useAddWorkflowStep(workflowId: string, options?: UseAddWorkflowStepOptions) {
  const mutation = useMutation({
    mutationFn: async (payload: AddWorkflowStepPayload) => {
      const response = await addWorkflowStepApi({
        path: { id: workflowId },
        body: payload,
      });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });
      notifications.show({
        title: "Step added",
        message: "The step has been added successfully.",
        color: "green",
      });
      options?.onSuccess?.();
    },
    onError: (error: Error & { response?: { status: number } }) => {
      const status = error.response?.status;
      let message = "An error occurred while adding the step.";

      if (status === 404) {
        message = "The workflow does not exist.";
      } else if (status === 400) {
        message = "The workflow is archived and cannot be modified.";
      } else if (status === 422) {
        message = "Validation error. Please check the step content.";
      }

      notifications.show({
        title: "Error",
        message,
        color: "red",
      });
    },
  });

  return {
    addStep: mutation.mutate,
    isAdding: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
