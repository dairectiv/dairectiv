import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { archiveRule as archiveRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseArchiveRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useArchiveRule(ruleId: string, options?: UseArchiveRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async () => {
      await archiveRuleApi({ path: { id: ruleId }, throwOnError: true });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Archiving rule",
        message: "Rule archived successfully",
        loadingMessage: "Archiving your rule...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Rule archived",
        message: "The rule has been archived successfully.",
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
          message: "The rule could not be found. It may have been deleted.",
        });
      } else if (status === 409) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot archive rule",
          message: "The rule is already archived.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error archiving rule",
          message: "An unexpected error occurred. Please try again.",
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
