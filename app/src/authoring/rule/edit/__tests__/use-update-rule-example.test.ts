import { act, renderHook, waitFor } from "@testing-library/react";
import type { AxiosError } from "axios";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock TanStack Query
const mockMutate = vi.fn();
const mockUseMutation = vi.fn();
vi.mock("@tanstack/react-query", () => ({
  useMutation: (options: unknown) => mockUseMutation(options),
}));

// Mock notification helpers
const mockShowLoadingNotification = vi.fn(() => "test-notification-id");
const mockUpdateToSuccess = vi.fn();
const mockUpdateToError = vi.fn();
vi.mock("@shared/ui/feedback/notification", () => ({
  showLoadingNotification: (opts: unknown) => mockShowLoadingNotification(opts),
  updateToSuccess: (id: string, opts: unknown) => mockUpdateToSuccess(id, opts),
  updateToError: (id: string, opts: unknown) => mockUpdateToError(id, opts),
}));

// Mock query client
vi.mock("@shared/infrastructure/query-client/query-client", () => ({
  queryClient: {
    invalidateQueries: vi.fn(),
  },
}));

// Mock API SDK
vi.mock("@shared/infrastructure/api/generated/sdk.gen", () => ({
  updateRuleExample: vi.fn(),
}));

// Mock query keys
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getRuleQueryKey: ({ path }: { path: { id: string } }) => ["rule", path.id],
}));

import { useUpdateRuleExample } from "../hooks/use-update-rule-example";

describe("useUpdateRuleExample", () => {
  const ruleId = "test-rule-id";
  const exampleId = "test-example-id";

  beforeEach(() => {
    vi.clearAllMocks();
    mockUseMutation.mockImplementation((options) => ({
      mutate: (variables: unknown) => {
        mockMutate(variables);
        const context = options.onMutate?.();
        options.onSuccess?.(undefined, variables, context);
      },
      isPending: false,
      isError: false,
      error: null,
    }));
  });

  it("should call mutation with correct payload", async () => {
    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    act(() => {
      result.current.updateExample(exampleId, {
        good: "Updated good example",
        bad: "Updated bad example",
        explanation: "Updated explanation",
      });
    });

    expect(mockMutate).toHaveBeenCalledWith({
      exampleId,
      payload: {
        good: "Updated good example",
        bad: "Updated bad example",
        explanation: "Updated explanation",
      },
    });
  });

  it("should show loading notification on mutate", async () => {
    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    act(() => {
      result.current.updateExample(exampleId, {
        good: "Good",
        bad: "Bad",
        explanation: null,
      });
    });

    await waitFor(() => {
      expect(mockShowLoadingNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Updating example",
          loadingMessage: "Saving example changes...",
        }),
      );
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    act(() => {
      result.current.updateExample(exampleId, {
        good: "Good",
        bad: "Bad",
        explanation: null,
      });
    });

    await waitFor(() => {
      expect(mockUpdateToSuccess).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Example updated",
        }),
      );
    });
  });

  it("should call onSuccess callback when provided", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useUpdateRuleExample(ruleId, { onSuccess }));

    act(() => {
      result.current.updateExample(exampleId, {
        good: "Good",
        bad: "Bad",
        explanation: null,
      });
    });

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
    });
  });

  it("should show not found error notification for 404 status", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = {
          response: { status: 404 },
        } as AxiosError;
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    act(() => {
      result.current.updateExample(exampleId, { good: "Good", bad: "Bad", explanation: null });
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Rule not found",
        }),
      );
    });
  });

  it("should show bad request error notification for 400 status", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = {
          response: { status: 400 },
        } as AxiosError;
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    act(() => {
      result.current.updateExample(exampleId, { good: "Good", bad: "Bad", explanation: null });
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Cannot update example",
        }),
      );
    });
  });

  it("should show generic error notification for other errors", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = {
          response: { status: 500 },
        } as AxiosError;
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    act(() => {
      result.current.updateExample(exampleId, { good: "Good", bad: "Bad", explanation: null });
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Error updating example",
        }),
      );
    });
  });

  it("should call onError callback when provided", async () => {
    const error = { response: { status: 500 } } as AxiosError;
    const onError = vi.fn();

    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: true,
      error,
    }));

    const { result } = renderHook(() => useUpdateRuleExample(ruleId, { onError }));

    act(() => {
      result.current.updateExample(exampleId, { good: "Good", bad: "Bad", explanation: null });
    });

    await waitFor(() => {
      expect(onError).toHaveBeenCalledWith(error);
    });
  });

  it("should return loading state", () => {
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: true,
      isError: false,
      error: null,
    });

    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    expect(result.current.isUpdating).toBe(true);
  });

  it("should return error state", () => {
    const error = new Error("Test error");
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useUpdateRuleExample(ruleId));

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });
});
