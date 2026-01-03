import {
  type UpdateWorkflowStepPayload,
  updateWorkflowStep as updateWorkflowStepApi,
} from "@shared/infrastructure/api/generated";
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
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Updating step",
        message: "Step updated successfully",
        loadingMessage: "Saving step changes...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Step updated",
        message: "The step has been updated successfully.",
      });

      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });
    },
    onError: (error: Error & { response?: { status: number } }, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Workflow not found",
          message: "The workflow does not exist.",
        });
      } else if (status === 400) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot update step",
          message: "The step does not exist or the workflow is archived.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error updating step",
          message: "An error occurred while updating the step.",
        });
      }
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
