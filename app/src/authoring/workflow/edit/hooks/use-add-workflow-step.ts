import {
  type AddWorkflowStepPayload,
  addWorkflowStep as addWorkflowStepApi,
} from "@shared/infrastructure/api/generated";
import { getWorkflowQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";

export interface UseAddWorkflowStepOptions {
  onSuccess?: () => void;
}

interface MutationContext {
  notificationId: string;
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
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Adding step",
        message: "Step added successfully",
        loadingMessage: "Adding step to workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Step added",
        message: "The step has been added successfully.",
      });

      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      options?.onSuccess?.();
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
          title: "Cannot add step",
          message: "The workflow is archived and cannot be modified.",
        });
      } else if (status === 422) {
        updateToError(context?.notificationId ?? "", {
          title: "Validation error",
          message: "Please check the step content.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error adding step",
          message: "An error occurred while adding the step.",
        });
      }
    },
  });

  return {
    addStep: mutation.mutate,
    isAdding: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
