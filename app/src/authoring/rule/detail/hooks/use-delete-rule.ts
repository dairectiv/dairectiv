import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { deleteRule as deleteRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDeleteRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useDeleteRule(ruleId: string, options?: UseDeleteRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await deleteRuleApi({ path: { id: ruleId }, throwOnError: true });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Deleting rule",
        message: "Rule deleted successfully",
        loadingMessage: "Deleting your rule...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Rule deleted",
        message: "The rule has been permanently deleted.",
      });

      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getRuleQueryKey({ path: { id: ruleId } }) });

      // Navigate back to rules list
      navigate({ to: "/authoring/rules" });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Rule not found",
          message: "The rule could not be found. It may have already been deleted.",
        });
      } else if (status === 409) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot delete rule",
          message: "The rule is already deleted.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error deleting rule",
          message: "An unexpected error occurred. Please try again.",
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
