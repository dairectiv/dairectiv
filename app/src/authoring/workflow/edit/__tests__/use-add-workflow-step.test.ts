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
  addWorkflowStep: vi.fn(),
}));

vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getWorkflowQueryKey: vi.fn(() => ["getWorkflow", "test-workflow-id"]),
}));

import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useAddWorkflowStep } from "../hooks/use-add-workflow-step";

describe("useAddWorkflowStep", () => {
  const workflowId = "test-workflow-id";

  beforeEach(() => {
    vi.clearAllMocks();
    mockUseMutation.mockImplementation(() => ({
      mutate: mockMutate,
      isPending: false,
      isError: false,
      error: null,
    }));
  });

  it("should return addStep function", () => {
    const { result } = renderHook(() => useAddWorkflowStep(workflowId));

    expect(result.current.addStep).toBeDefined();
    expect(typeof result.current.addStep).toBe("function");
  });

  it("should return isAdding state", () => {
    const { result } = renderHook(() => useAddWorkflowStep(workflowId));

    expect(result.current.isAdding).toBe(false);
  });

  it("should return isError state", () => {
    const { result } = renderHook(() => useAddWorkflowStep(workflowId));

    expect(result.current.isError).toBe(false);
  });

  it("should call mutate when addStep is called", () => {
    const { result } = renderHook(() => useAddWorkflowStep(workflowId));

    result.current.addStep({ content: "Step content" });

    expect(mockMutate).toHaveBeenCalledWith({ content: "Step content" });
  });

  it("should pass onSuccess callback to hook options", () => {
    const onSuccess = vi.fn();
    renderHook(() => useAddWorkflowStep(workflowId, { onSuccess }));

    const mutationOptions = mockUseMutation.mock.calls[0][0];
    expect(mutationOptions.onSuccess).toBeDefined();
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

    renderHook(() => useAddWorkflowStep(workflowId));

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

    renderHook(() => useAddWorkflowStep(workflowId));

    // Simulate success
    successCallback!();

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Step added",
          color: "green",
        }),
      );
    });
  });

  it("should call user onSuccess callback on success", async () => {
    const onSuccess = vi.fn();
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

    renderHook(() => useAddWorkflowStep(workflowId, { onSuccess }));

    // Simulate success
    successCallback!();

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
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

    renderHook(() => useAddWorkflowStep(workflowId));

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

    renderHook(() => useAddWorkflowStep(workflowId));

    // Simulate 400 error
    const error = new Error("Bad request") as Error & { response?: { status: number } };
    error.response = { status: 400 };
    errorCallback!(error);

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "The workflow is archived and cannot be modified.",
          color: "red",
        }),
      );
    });
  });

  it("should show error notification on 422 error", async () => {
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

    renderHook(() => useAddWorkflowStep(workflowId));

    // Simulate 422 error
    const error = new Error("Validation error") as Error & { response?: { status: number } };
    error.response = { status: 422 };
    errorCallback!(error);

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "Validation error. Please check the step content.",
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

    renderHook(() => useAddWorkflowStep(workflowId));

    // Simulate unknown error
    errorCallback!(new Error("Unknown error"));

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "An error occurred while adding the step.",
          color: "red",
        }),
      );
    });
  });
});
