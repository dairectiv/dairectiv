import { listRulesQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { draftRule as draftRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { DraftRulePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDraftRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

interface MutationContext {
  notificationId: string;
}

export function useDraftRule(options?: UseDraftRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async (payload: DraftRulePayload) => {
      const { data } = await draftRuleApi({ body: payload, throwOnError: true });
      return data;
    },
    onMutate: (): MutationContext => {
      const notificationId = showLoadingNotification({
        title: "Creating rule",
        message: "Rule created successfully",
        loadingMessage: "Creating your rule...",
      });
      return { notificationId };
    },
    onSuccess: (_data, _variables, context) => {
      updateToSuccess(context.notificationId, {
        title: "Rule created",
        message: "Your rule has been created successfully.",
      });

      // Invalidate the rules list to refresh data
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });

      // Navigate to rules list
      navigate({ to: "/authoring/rules" });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError, _variables, context) => {
      const status = error.response?.status;

      if (status === 409) {
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
          title: "Error creating rule",
          message: "An unexpected error occurred. Please try again.",
        });
      }

      options?.onError?.(error);
    },
  });

  const createRule = (payload: DraftRulePayload) => {
    mutation.mutate(payload);
  };

  return {
    draftRule: createRule,
    isLoading: mutation.isPending,
    isError: mutation.isError,
    error: mutation.error,
  };
}
