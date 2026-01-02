import { act, renderHook, waitFor } from "@testing-library/react";
import type { AxiosError } from "axios";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock TanStack Router
const mockNavigate = vi.fn();
vi.mock("@tanstack/react-router", () => ({
  useNavigate: () => mockNavigate,
}));

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
  deleteRule: vi.fn(),
}));

// Mock query keys
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listRulesQueryKey: () => ["rules"],
  getRuleQueryKey: (opts: { path: { id: string } }) => ["rule", opts.path.id],
}));

import { useDeleteRule } from "../hooks/use-delete-rule";

describe("useDeleteRule", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        mockMutate();
        // Simulate success by default
        options.onSuccess?.();
      },
      isPending: false,
      isError: false,
      error: null,
    }));
  });

  it("should call mutation when deleteRule is called", async () => {
    const { result } = renderHook(() => useDeleteRule("rule-123"));

    act(() => {
      result.current.deleteRule();
    });

    expect(mockMutate).toHaveBeenCalled();
  });

  it("should navigate to rules list on success", async () => {
    const { result } = renderHook(() => useDeleteRule("rule-123"));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({ to: "/authoring/rules" });
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useDeleteRule("rule-123"));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Rule deleted",
          color: "green",
        }),
      );
    });
  });

  it("should call onSuccess callback when provided", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useDeleteRule("rule-123", { onSuccess }));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
    });
  });

  it("should show not found notification for 404 status", async () => {
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

    const { result } = renderHook(() => useDeleteRule("rule-123"));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Rule not found",
          color: "red",
        }),
      );
    });
  });

  it("should show conflict notification for 409 status", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const error = {
          response: { status: 409 },
        } as AxiosError;
        options.onError?.(error);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useDeleteRule("rule-123"));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Cannot delete rule",
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

    const { result } = renderHook(() => useDeleteRule("rule-123"));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Error deleting rule",
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

    const { result } = renderHook(() => useDeleteRule("rule-123", { onError }));

    act(() => {
      result.current.deleteRule();
    });

    await waitFor(() => {
      expect(onError).toHaveBeenCalledWith(error);
    });
  });

  it("should return isDeleting state when pending", () => {
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: true,
      isError: false,
      error: null,
    });

    const { result } = renderHook(() => useDeleteRule("rule-123"));

    expect(result.current.isDeleting).toBe(true);
  });

  it("should return error state", () => {
    const error = new Error("Test error");
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useDeleteRule("rule-123"));

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });
});
