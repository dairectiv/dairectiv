import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateRule as updateRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateRulePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseUpdateRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useUpdateRule(ruleId: string, options?: UseUpdateRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async (payload: UpdateRulePayload) => {
      const { data } = await updateRuleApi({
        path: { id: ruleId },
        body: payload,
        throwOnError: true,
      });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Updating rule",
        message: "Rule updated successfully",
        loadingMessage: "Saving your changes...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Rule updated",
        message: "Your rule has been updated successfully.",
      });

      // Invalidate both the rules list and the specific rule detail
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getRuleQueryKey({ path: { id: ruleId } }),
      });

      // Navigate to rule detail page
      navigate({ to: "/authoring/rules/$ruleId", params: { ruleId } });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 404) {
        updateToError(context?.notificationId ?? "", {
          title: "Rule not found",
          message: "The rule you are trying to update does not exist.",
        });
      } else if (status === 409) {
        updateToError(context?.notificationId ?? "", {
          title: "Rule already exists",
          message: "A rule with this name already exists. Please choose a different name.",
        });
      } else if (status === 422) {
        updateToError(context?.notificationId ?? "", {
          title: "Validation error",
          message: "Please check the form fields and try again.",
        });
      } else {
        updateToError(context?.notificationId ?? "", {
          title: "Error updating rule",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const updateRule = (payload: UpdateRulePayload) => {
    mutation.mutate(payload);
  };

  return {
    updateRule,
    isUpdating: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
