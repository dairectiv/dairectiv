import { notifications } from "@mantine/notifications";
import { getRuleQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { removeRuleExample as removeRuleExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseRemoveRuleExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useRemoveRuleExample(ruleId: string, options?: UseRemoveRuleExampleOptions) {
  const mutation = useMutation({
    mutationFn: async (exampleId: string) => {
      const { data } = await removeRuleExampleApi({
        path: { id: ruleId, exampleId },
        throwOnError: true,
      });
      return data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: getRuleQueryKey({ path: { id: ruleId } }),
      });

      notifications.show({
        title: "Example removed",
        message: "The example has been removed from the rule.",
        color: "green",
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Not found",
          message: "The rule or example was not found.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error removing example",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const removeExample = (exampleId: string) => {
    mutation.mutate(exampleId);
  };

  return {
    removeExample,
    isRemoving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
