import { notifications } from "@mantine/notifications";
import {
  getWorkflowQueryKey,
  listWorkflowsQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateWorkflow as updateWorkflowApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateWorkflowPayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseUpdateWorkflowOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
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
    onSuccess: () => {
      // Invalidate both the workflows list and the specific workflow detail
      queryClient.invalidateQueries({ queryKey: listWorkflowsQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getWorkflowQueryKey({ path: { id: workflowId } }),
      });

      notifications.show({
        title: "Workflow updated",
        message: "Your workflow has been updated successfully.",
        color: "green",
      });

      // Navigate to workflow detail page
      navigate({ to: "/authoring/workflows/$workflowId", params: { workflowId } });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Workflow not found",
          message: "The workflow you are trying to update does not exist.",
          color: "red",
        });
      } else if (status === 409) {
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
          title: "Error updating workflow",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
