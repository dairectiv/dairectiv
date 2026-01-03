import { getRuleQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateRuleExample as updateRuleExampleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateRuleExamplePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import type { AxiosError } from "axios";

export interface UseUpdateRuleExampleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
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
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Updating example",
        message: "Example updated successfully",
        loadingMessage: "Saving example changes...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Example updated",
        message: "The example has been updated successfully.",
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
          message: "The rule you are trying to update does not exist.",
        });
      } else if (status === 400) {
        updateToError(context?.notificationId ?? "", {
          title: "Cannot update example",
          message: "The example was not found or the rule is archived.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error updating example",
          message: "An unexpected error occurred. Please try again.",
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
