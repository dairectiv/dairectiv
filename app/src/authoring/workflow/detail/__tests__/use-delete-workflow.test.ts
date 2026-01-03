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
  deleteWorkflow: vi.fn(),
}));

// Mock query keys
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listWorkflowsQueryKey: () => ["workflows"],
  getWorkflowQueryKey: (opts: { path: { id: string } }) => ["workflow", opts.path.id],
}));

import { useDeleteWorkflow } from "../hooks/use-delete-workflow";

describe("useDeleteWorkflow", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        mockMutate();
        const context = options.onMutate?.();
        options.onSuccess?.(undefined, undefined, context);
      },
      isPending: false,
      isError: false,
      error: null,
    }));
  });

  it("should call mutation when deleteWorkflow is called", async () => {
    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    expect(mockMutate).toHaveBeenCalled();
  });

  it("should navigate to workflows list on success", async () => {
    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({ to: "/authoring/workflows" });
    });
  });

  it("should show loading notification on mutate", async () => {
    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(mockShowLoadingNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Deleting workflow",
          loadingMessage: "Deleting your workflow...",
        }),
      );
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(mockUpdateToSuccess).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Workflow deleted",
        }),
      );
    });
  });

  it("should call onSuccess callback when provided", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useDeleteWorkflow("workflow-123", { onSuccess }));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
    });
  });

  it("should show not found notification for 404 status", async () => {
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

    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Workflow not found",
        }),
      );
    });
  });

  it("should show conflict notification for 409 status", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = {
          response: { status: 409 },
        } as AxiosError;
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Cannot delete workflow",
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

    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    act(() => {
      result.current.deleteWorkflow();
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Error deleting workflow",
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

    const { result } = renderHook(() => useDeleteWorkflow("workflow-123", { onError }));

    act(() => {
      result.current.deleteWorkflow();
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

    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

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

    const { result } = renderHook(() => useDeleteWorkflow("workflow-123"));

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });
});
