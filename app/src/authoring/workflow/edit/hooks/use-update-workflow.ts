import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateWorkflow as updateWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateWorkflowPayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseUpdateWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useUpdateWorkflow(workflowId: string, options?: UseUpdateWorkflowOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async (payload: UpdateWorkflowPayload) => {
      const { data } = await updateWorkflowApi({
        path: { id: workflowId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Updating workflow",
        message: "Workflow updated successfully",
        loadingMessage: "Saving your changes...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Workflow updated",
        message: "Your workflow has been updated successfully.",
      });

      // Invalidate both the workflows list and the specific workflow detail
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      // Navigate to workflow detail page
      navigate({ to: "/authoring/workflows/$workflowId", params: { workflowId } });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Workflow not found",
          message: "The workflow you are trying to update does not exist.",
        });
      } else if (status === 409) {
        updateToError(context?.notificationId ?? "", {
          title: "Workflow already exists",
          message: "A workflow with this name already exists. Please choose a different name.",
        });
      } else if (status === 422) {
        updateToError(context?.notificationId ?? "", {
          title: "Validation error",
          message: "Please check the form fields and try again.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error updating workflow",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const updateWorkflow = (payload: UpdateWorkflowPayload) => {
    mutation.mutate(payload);
  };

  return {
    updateWorkflow,
    isUpdating: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
