import { renderHook, waitFor } from "@testing-library/react";
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
  removeWorkflowStep: vi.fn(),
}));

vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getWorkflowQueryKey: vi.fn(() => ["getWorkflow", "test-workflow-id"]),
}));

import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useRemoveWorkflowStep } from "../hooks/use-remove-workflow-step";

describe("useRemoveWorkflowStep", () => {
  const workflowId = "test-workflow-id";
  const stepId = "test-step-id";

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

  it("should return removeStep function", () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    expect(result.current.removeStep).toBeDefined();
    expect(typeof result.current.removeStep).toBe("function");
  });

  it("should return isRemoving state", () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    expect(result.current.isRemoving).toBe(false);
  });

  it("should return isError state", () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    expect(result.current.isError).toBe(false);
  });

  it("should call mutate when removeStep is called", () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    result.current.removeStep(stepId);

    expect(mockMutate).toHaveBeenCalledWith(stepId);
  });

  it("should invalidate query cache on success", async () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    result.current.removeStep(stepId);

    await waitFor(() => {
      expect(queryClient.invalidateQueries).toHaveBeenCalled();
    });
  });

  it("should show loading notification on mutate", async () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    result.current.removeStep(stepId);

    await waitFor(() => {
      expect(mockShowLoadingNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Removing step",
          loadingMessage: "Removing step from workflow...",
        }),
      );
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    result.current.removeStep(stepId);

    await waitFor(() => {
      expect(mockUpdateToSuccess).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Step removed",
        }),
      );
    });
  });

  it("should show error notification on 404 error", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = new Error("Not found") as Error & { response?: { status: number } };
        error.response = { status: 404 };
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: false,
      error: null,
    }));

    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    result.current.removeStep(stepId);

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Not found",
        }),
      );
    });
  });

  it("should show generic error notification on unknown error", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        options.onError?.(new Error("Unknown error"), undefined, context);
      },
      isPending: false,
      isError: false,
      error: null,
    }));

    const { result } = renderHook(() => useRemoveWorkflowStep(workflowId));

    result.current.removeStep(stepId);

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Error removing step",
        }),
      );
    });
  });
});
