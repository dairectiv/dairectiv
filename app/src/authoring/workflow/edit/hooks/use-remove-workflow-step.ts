import { removeWorkflowStep as removeWorkflowStepApi } from "@shared/infrastructure/api/generated";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";

interface MutationContext {
  notificationId: string;
}

export function useRemoveWorkflowStep(workflowId: string) {
  const mutation = useMutation({
    mutationFn: async (stepId: string) => {
      const response = await removeWorkflowStepApi({
        path: { id: workflowId, stepId },
      });
      return response.data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Removing step",
        message: "Step removed successfully",
        loadingMessage: "Removing step from workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Step removed",
        message: "The step has been removed successfully.",
      });

      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });
    },
    onError: (error: Error & { response?: { status: number } }, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Not found",
          message: "The workflow or step does not exist.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error removing step",
          message: "An error occurred while removing the step.",
        });
      }
    },
  });

  return {
    removeStep: mutation.mutate,
    isRemoving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
