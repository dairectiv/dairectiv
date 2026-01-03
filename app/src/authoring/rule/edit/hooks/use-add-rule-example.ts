import { getRuleQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { addRuleExample as addRuleExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { AddRuleExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseAddRuleExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useAddRuleExample(ruleId: string, options?: UseAddRuleExampleOptions) {
  const mutation = useMutation({
    mutationFn: async (payload: AddRuleExamplePayload) => {
      const { data } = await addRuleExampleApi({
        path: { id: ruleId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Adding example",
        message: "Example added successfully",
        loadingMessage: "Adding example to rule...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Example added",
        message: "The example has been added to the rule.",
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
          title: "Rule not found",
          message: "The rule you are trying to add an example to does not exist.",
        });
      } else if (status === 400) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot add example",
          message: "The rule is archived and cannot be modified.",
        });
      } else if (status === 422) {
        updateToError(context?.notificationId ?? "", {
          title: "Validation error",
          message: "Please check the example fields and try again.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error adding example",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const addExample = (payload: AddRuleExamplePayload) => {
    mutation.mutate(payload);
  };

  return {
    addExample,
    isAdding: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
