import { notifications } from "@mantine/notifications";
import { getRuleQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateRuleExample as updateRuleExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateRuleExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseUpdateRuleExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useUpdateRuleExample(ruleId: string, options?: UseUpdateRuleExampleOptions) {
  const mutation = useMutation({
    mutationFn: async ({
      exampleId,
      payload,
    }: {
      exampleId: string;
      payload: UpdateRuleExamplePayload;
    }) => {
      const { data } = await updateRuleExampleApi({
        path: { id: ruleId, exampleId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getRuleQueryKey({ path: { id: ruleId } }),
      });

      notifications.show({
        title: "Example updated",
        message: "The example has been updated successfully.",
        color: "green",
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Rule not found",
          message: "The rule you are trying to update does not exist.",
          color: "red",
        });
      } else if (status === 400) {
        notifications.show({
          title: "Cannot update example",
          message: "The example was not found or the rule is archived.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error updating example",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const updateExample = (exampleId: string, payload: UpdateRuleExamplePayload) => {
    mutation.mutate({ exampleId, payload });
  };

  return {
    updateExample,
    isUpdating: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
