import { getRuleQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { removeRuleExample as removeRuleExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseRemoveRuleExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
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
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Removing example",
        message: "Example removed successfully",
        loadingMessage: "Removing example from rule...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Example removed",
        message: "The example has been removed from the rule.",
      });

      queryClient.invalidateQueries({
        queryKey: getRuleQueryKey({ path: { id: ruleId } }),
      });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Not found",
          message: "The rule or example was not found.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error removing example",
          message: "An unexpected error occurred. Please try again.",
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
