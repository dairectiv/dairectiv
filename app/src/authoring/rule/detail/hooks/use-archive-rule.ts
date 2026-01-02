import { notifications } from "@mantine/notifications";
import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { archiveRule as archiveRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseArchiveRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useArchiveRule(ruleId: string, options?: UseArchiveRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await archiveRuleApi({ path: { id: ruleId }, throwOnError: true });
    },
    onSuccess: () => {
      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getRuleQueryKey({ path: { id: ruleId } }) });

      notifications.show({
        title: "Rule archived",
        message: "The rule has been archived successfully.",
        color: "green",
      });

      // Navigate back to rules list
      navigate({ to: "/authoring/rules" });

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
          title: "Cannot archive rule",
          message: "The rule is already archived.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error archiving rule",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const archiveRule = () => {
    mutation.mutate();
  };

  return {
    archiveRule,
    isArchiving: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
