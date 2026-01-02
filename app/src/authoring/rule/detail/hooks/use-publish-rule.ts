import { notifications } from "@mantine/notifications";
import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { publishRule as publishRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UsePublishRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function usePublishRule(ruleId: string, options?: UsePublishRuleOptions) {
  const mutation = useMutation({
    mutationFn: async () => {
      await publishRuleApi({ path: { id: ruleId }, throwOnError: true });
    },
    onSuccess: () => {
      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getRuleQueryKey({ path: { id: ruleId } }) });

      notifications.show({
        title: "Rule published",
        message: "The rule has been published and is now available to AI tools.",
        color: "green",
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Rule not found",
          message: "The rule could not be found. It may have been deleted.",
          color: "red",
        });
      } else if (status === 409) {
        notifications.show({
          title: "Cannot publish rule",
          message: "The rule is not in draft state and cannot be published.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error publishing rule",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const publishRule = () => {
    mutation.mutate();
  };

  return {
    publishRule,
    isPublishing: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
