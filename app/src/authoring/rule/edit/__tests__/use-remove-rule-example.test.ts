import { act, renderHook, waitFor } from "@testing-library/react";
import type { AxiosError } from "axios";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock TanStack Query
const mockMutate = vi.fn();
const mockUseMutation = vi.fn();
vi.mock("@tanstack/react-query", () => ({
  useMutation: (options: unknown) => mockUseMutation(options),
}));

// Mock notifications
const mockShowNotification = vi.fn();
vi.mock("@mantine/notifications", () => ({
  notifications: {
    show: (opts: unknown) => mockShowNotification(opts),
  },
}));

// Mock query client
vi.mock("@shared/infrastructure/query-client/query-client", () => ({
  queryClient: {
    invalidateQueries: vi.fn(),
  },
}));

// Mock API SDK
vi.mock("@shared/infrastructure/api/generated/sdk.gen", () => ({
  removeRuleExample: vi.fn(),
}));

// Mock query keys
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getRuleQueryKey: ({ path }: { path: { id: string } }) => ["rule", path.id],
}));

import { useRemoveRuleExample } from "../hooks/use-remove-rule-example";

describe("useRemoveRuleExample", () => {
  const ruleId = "test-rule-id";
  const exampleId = "test-example-id";

  beforeEach(() => {
    vi.clearAllMocks();
    mockUseMutation.mockImplementation((options) => ({
      mutate: (variables: unknown) => {
        mockMutate(variables);
        options.onSuccess?.();
      },
      isPending: false,
      isError: false,
      error: null,
    }));
  });

  it("should call mutation with example id", async () => {
    const { result } = renderHook(() => useRemoveRuleExample(ruleId));

    act(() => {
      result.current.removeExample(exampleId);
    });

    expect(mockMutate).toHaveBeenCalledWith(exampleId);
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useRemoveRuleExample(ruleId));

    act(() => {
      result.current.removeExample(exampleId);
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Example removed",
          color: "green",
        }),
      );
    });
  });

  it("should call onSuccess callback when provided", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useRemoveRuleExample(ruleId, { onSuccess }));

    act(() => {
      result.current.removeExample(exampleId);
    });

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
    });
  });

  it("should show not found error notification for 404 status", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const error = {
          response: { status: 404 },
        } as AxiosError;
        options.onError?.(error);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useRemoveRuleExample(ruleId));

    act(() => {
      result.current.removeExample(exampleId);
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Not found",
          color: "red",
        }),
      );
    });
  });

  it("should show generic error notification for other errors", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const error = {
          response: { status: 500 },
        } as AxiosError;
        options.onError?.(error);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useRemoveRuleExample(ruleId));

    act(() => {
      result.current.removeExample(exampleId);
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Error removing example",
          color: "red",
        }),
      );
    });
  });

  it("should call onError callback when provided", async () => {
    const error = { response: { status: 500 } } as AxiosError;
    const onError = vi.fn();

    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        options.onError?.(error);
      },
      isPending: false,
      isError: true,
      error,
    }));

    const { result } = renderHook(() => useRemoveRuleExample(ruleId, { onError }));

    act(() => {
      result.current.removeExample(exampleId);
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

    const { result } = renderHook(() => useRemoveRuleExample(ruleId));

    expect(result.current.isRemoving).toBe(true);
  });

  it("should return error state", () => {
    const error = new Error("Test error");
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useRemoveRuleExample(ruleId));

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });
});
