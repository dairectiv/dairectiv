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
    mockUseMutation.mockImplementation(() => ({
      mutate: mockMutate,
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

    renderHook(() => useRemoveWorkflowStep(workflowId));

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

    renderHook(() => useRemoveWorkflowStep(workflowId));

    // Simulate success
    successCallback!();

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Step removed",
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

    renderHook(() => useRemoveWorkflowStep(workflowId));

    // Simulate 404 error
    const error = new Error("Not found") as Error & { response?: { status: number } };
    error.response = { status: 404 };
    errorCallback!(error);

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "The workflow or step does not exist.",
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

    renderHook(() => useRemoveWorkflowStep(workflowId));

    // Simulate unknown error
    errorCallback!(new Error("Unknown error"));

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "An error occurred while removing the step.",
          color: "red",
        }),
      );
    });
  });
});
