import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { publishRule as publishRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UsePublishRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function usePublishRule(ruleId: string, options?: UsePublishRuleOptions) {
  const mutation = useMutation({
    mutationFn: async () => {
      await publishRuleApi({ path: { id: ruleId }, throwOnError: true });
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Publishing rule",
        message: "Rule published successfully",
        loadingMessage: "Publishing your rule...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Rule published",
        message: "The rule has been published and is now available to AI tools.",
      });

      // Invalidate both the list and detail queries
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({ queryKey: getRuleQueryKey({ path: { id: ruleId } }) });

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
          title: "Cannot publish rule",
          message: "The rule is not in draft state and cannot be published.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error publishing rule",
          message: "An unexpected error occurred. Please try again.",
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
