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
  updateWorkflowStep: vi.fn(),
}));

vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getWorkflowQueryKey: vi.fn(() => ["getWorkflow", "test-workflow-id"]),
}));

import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useUpdateWorkflowStep } from "../hooks/use-update-workflow-step";

describe("useUpdateWorkflowStep", () => {
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

  it("should return updateStep function", () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    expect(result.current.updateStep).toBeDefined();
    expect(typeof result.current.updateStep).toBe("function");
  });

  it("should return isUpdating state", () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    expect(result.current.isUpdating).toBe(false);
  });

  it("should return isError state", () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    expect(result.current.isError).toBe(false);
  });

  it("should call mutate when updateStep is called", () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    expect(mockMutate).toHaveBeenCalledWith({ stepId, payload: { content: "Updated content" } });
  });

  it("should invalidate query cache on success", async () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    await waitFor(() => {
      expect(queryClient.invalidateQueries).toHaveBeenCalled();
    });
  });

  it("should show loading notification on mutate", async () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    await waitFor(() => {
      expect(mockShowLoadingNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Updating step",
          loadingMessage: "Saving step changes...",
        }),
      );
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    await waitFor(() => {
      expect(mockUpdateToSuccess).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Step updated",
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

    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Workflow not found",
        }),
      );
    });
  });

  it("should show error notification on 400 error", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = new Error("Bad request") as Error & { response?: { status: number } };
        error.response = { status: 400 };
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: false,
      error: null,
    }));

    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Cannot update step",
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

    const { result } = renderHook(() => useUpdateWorkflowStep(workflowId));

    result.current.updateStep(stepId, { content: "Updated content" });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Error updating step",
        }),
      );
    });
  });
});
