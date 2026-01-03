import { listWorkflowsQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { draftWorkflow as draftWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { DraftWorkflowPayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDraftWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useDraftWorkflow(options?: UseDraftWorkflowOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async (payload: DraftWorkflowPayload) => {
      const { data } = await draftWorkflowApi({ body: payload, throwOnError: true });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Creating workflow",
        message: "Workflow created successfully",
        loadingMessage: "Creating your workflow...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Workflow created",
        message: "Your workflow has been created successfully.",
      });

      // Invalidate the workflows list to refresh data
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });

      // Navigate to workflows list
      navigate({ to: "/authoring/workflows" });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 409) {
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
          title: "Error creating workflow",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const createWorkflow = (payload: DraftWorkflowPayload) => {
    mutation.mutate(payload);
  };

  return {
    draftWorkflow: createWorkflow,
    isLoading: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
