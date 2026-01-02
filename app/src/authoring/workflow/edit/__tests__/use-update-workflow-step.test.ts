import { renderHook, waitFor } from "@testing-library/react";
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
    mockUseMutation.mockImplementation(() => ({
      mutate: mockMutate,
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
    let successCallback: () => void;
    mockUseMutation.mockImplementation((options) => {
      successCallback = options.onSuccess;
      return {
        mutate: mockMutate,
        isPending: false,
        isError: false,
        error: null,
      };
    });

    renderHook(() => useUpdateWorkflowStep(workflowId));

    // Simulate success
    successCallback!();

    await waitFor(() => {
      expect(queryClient.invalidateQueries).toHaveBeenCalled();
    });
  });

  it("should show success notification on success", async () => {
    let successCallback: () => void;
    mockUseMutation.mockImplementation((options) => {
      successCallback = options.onSuccess;
      return {
        mutate: mockMutate,
        isPending: false,
        isError: false,
        error: null,
      };
    });

    renderHook(() => useUpdateWorkflowStep(workflowId));

    // Simulate success
    successCallback!();

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Step updated",
          color: "green",
        }),
      );
    });
  });

  it("should show error notification on 404 error", async () => {
    let errorCallback: (error: Error & { response?: { status: number } }) => void;
    mockUseMutation.mockImplementation((options) => {
      errorCallback = options.onError;
      return {
        mutate: mockMutate,
        isPending: false,
        isError: false,
        error: null,
      };
    });

    renderHook(() => useUpdateWorkflowStep(workflowId));

    // Simulate 404 error
    const error = new Error("Not found") as Error & { response?: { status: number } };
    error.response = { status: 404 };
    errorCallback!(error);

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "The workflow does not exist.",
          color: "red",
        }),
      );
    });
  });

  it("should show error notification on 400 error", async () => {
    let errorCallback: (error: Error & { response?: { status: number } }) => void;
    mockUseMutation.mockImplementation((options) => {
      errorCallback = options.onError;
      return {
        mutate: mockMutate,
        isPending: false,
        isError: false,
        error: null,
      };
    });

    renderHook(() => useUpdateWorkflowStep(workflowId));

    // Simulate 400 error
    const error = new Error("Bad request") as Error & { response?: { status: number } };
    error.response = { status: 400 };
    errorCallback!(error);

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "The step does not exist or the workflow is archived.",
          color: "red",
        }),
      );
    });
  });

  it("should show generic error notification on unknown error", async () => {
    let errorCallback: (error: Error) => void;
    mockUseMutation.mockImplementation((options) => {
      errorCallback = options.onError;
      return {
        mutate: mockMutate,
        isPending: false,
        isError: false,
        error: null,
      };
    });

    renderHook(() => useUpdateWorkflowStep(workflowId));

    // Simulate unknown error
    errorCallback!(new Error("Unknown error"));

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "An error occurred while updating the step.",
          color: "red",
        }),
      );
    });
  });
});
