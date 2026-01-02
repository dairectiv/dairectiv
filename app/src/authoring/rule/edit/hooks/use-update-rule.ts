import { notifications } from "@mantine/notifications";
import {
  getRuleQueryKey,
  listRulesQueryKey,
} from "@shared/infrastructure/api/generated/@tanstack/react-query.gen";
import { updateRule as updateRuleApi } from "@shared/infrastructure/api/generated/sdk.gen";
import type { UpdateRulePayload } from "@shared/infrastructure/api/generated/types.gen";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMutation } from "@tanstack/react-query";
import { useNavigate } from "@tanstack/react-router";
import type { AxiosError } from "axios";

export interface UseUpdateRuleOptions {
  onSuccess?: () => void;
  onError?: (error: AxiosError) => void;
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
    onSuccess: () => {
      // Invalidate both the rules list and the specific rule detail
      queryClient.invalidateQueries({ queryKey: listRulesQueryKey() });
      queryClient.invalidateQueries({
        queryKey: getRuleQueryKey({ path: { id: ruleId } }),
      });

      notifications.show({
        title: "Rule updated",
        message: "Your rule has been updated successfully.",
        color: "green",
      });

      // Navigate to rule detail page
      navigate({ to: "/authoring/rules/$ruleId", params: { ruleId } });

      options?.onSuccess?.();
    },
    onError: (error: AxiosError) => {
      const status = error.response?.status;

      if (status === 404) {
        notifications.show({
          title: "Rule not found",
          message: "The rule you are trying to update does not exist.",
          color: "red",
        });
      } else if (status === 409) {
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
          title: "Error updating rule",
          message: "An unexpected error occurred. Please try again.",
          color: "red",
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
