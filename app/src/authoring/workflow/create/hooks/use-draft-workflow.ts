import { notifications } from "@mantine/notifications";
import { listWorkflowsQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { draftWorkflow as draftWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { DraftWorkflowPayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDraftWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useDraftWorkflow(options?: UseDraftWorkflowOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async (payload: DraftWorkflowPayload) => {
      const { data } = await draftWorkflowApi({ body: payload, throwOnError: true });
      return data;
    },
    onSuccess: () => {
      // Invalidate the workflows list to refresh data
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });

      notifications.show({
        title: "Workflow created",
        message: "Your workflow has been created successfully.",
        color: "green",
      });

      // Navigate to workflows list (detail page doesn't exist yet)
      navigate({ to: "/authoring/workflows" });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 409) {
        notifications.show({
          title: "Workflow already exists",
          message: "A workflow with this name already exists. Please choose a different name.",
          color: "red",
        });
      } else if (status === 422) {
        notifications.show({
          title: "Validation error",
          message: "Please check the form fields and try again.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error creating workflow",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
