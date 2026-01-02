import { notifications } from "@mantine/notifications";
import { removeWorkflowStep as removeWorkflowStepApi } from "@shared/infrastructure/api/generated";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";

export function useRemoveWorkflowStep(workflowId: string) {
  const mutation = useMutation({
    mutationFn: async (stepId: string) => {
      const response = await removeWorkflowStepApi({
        path: { id: workflowId, stepId },
      });
      return response.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });
      notifications.show({
        title: "Step removed",
        message: "The step has been removed successfully.",
        color: "green",
      });
    },
    onError: (error: Error & { response?: { status: number } }) => {
      const status = error.response?.status;
      let message = "An error occurred while removing the step.";

      if (status === 404) {
        message = "The workflow or step does not exist.";
      }

      notifications.show({
        title: "Error",
        message,
        color: "red",
      });
    },
  });

  return {
    removeStep: mutation.mutate,
    isRemoving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
