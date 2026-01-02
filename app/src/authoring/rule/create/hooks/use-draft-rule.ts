import { notifications } from "@mantine/notifications";
import { listRulesQueryKey } from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { draftRule as draftRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { DraftRulePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseDraftRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
}

export function useDraftRule(options?: UseDraftRuleOptions) {
  const navigate = useNavigate();

  const mutation = useMutation({
    mutationFn: async (payload: DraftRulePayload) => {
      const { data } = await draftRuleApi({ body: payload, throwOnError: true });
      return data;
    },
    onSuccess: () => {
      // Invalidate the rules list to refresh data
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });

      notifications.show({
        title: "Rule created",
        message: "Your rule has been created successfully.",
        color: "green",
      });

      // Navigate to rules list (detail page doesn't exist yet)
      navigate({ to: "/authoring/rules" });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 409) {
        notifications.show({
          title: "Rule already exists",
          message: "A rule with this name already exists. Please choose a different name.",
          color: "red",
        });
      } else if (status === 422) {
        notifications.show({
          title: "Validation error",
          message: "Please check the form fields and try again.",
          color: "red",
        });
      } else {
        notifications.show({
          title: "Error creating rule",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
