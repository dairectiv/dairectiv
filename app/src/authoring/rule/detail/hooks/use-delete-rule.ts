import { notifications } from "@mantine/notifications";
import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { deleteRule as deleteRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDeleteRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useDeleteRule(ruleId: string, options?: UseDeleteRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await deleteRuleApi({ path: { id: ruleId }, throwOnError: true });
    },
    onSuccess: () => {
      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getRuleQueryKey({ path: { id: ruleId } }) });

      notifications.show({
        title: "Rule deleted",
        message: "The rule has been permanently deleted.",
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
          message: "The rule could not be found. It may have already been deleted.",
          color: "red",
        });
      } else if (status === 409) {
        notifications.show({
          title: "Cannot delete rule",
          message: "The rule is already deleted.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error deleting rule",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
        });
      }

      options?.onError?.(error);
    },
  });

  const deleteRule = () => {
    mutation.mutate();
  };

  return {
    deleteRule,
    isDeleting: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
