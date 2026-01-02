import { renderHook } from "@testing-library/react";
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
  moveWorkflowStep: vi.fn(),
}));

vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getWorkflowQueryKey: vi.fn(() => ["getWorkflow", "test-workflow-id"]),
}));

import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useMoveWorkflowStep } from "../hooks/use-move-workflow-step";

describe("useMoveWorkflowStep", () => {
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

  it("should call mutation with step id and afterStepId", () => {
    const { result } = renderHook(() => useMoveWorkflowStep(workflowId));

    result.current.moveStep("step-1", "step-2");

    expect(mockMutate).toHaveBeenCalledWith({
      stepId: "step-1",
      payload: { afterStepId: "step-2" },
    });
  });

  it("should call mutation with null afterStepId for first position", () => {
    const { result } = renderHook(() => useMoveWorkflowStep(workflowId));

    result.current.moveStep("step-1", null);

    expect(mockMutate).toHaveBeenCalledWith({
      stepId: "step-1",
      payload: { afterStepId: null },
    });
  });

  it("should return isMoving based on mutation isPending", () => {
    mockUseMutation.mockImplementation(() => ({
      mutate: mockMutate,
      isPending: true,
      isError: false,
      error: null,
    }));

    const { result } = renderHook(() => useMoveWorkflowStep(workflowId));

    expect(result.current.isMoving).toBe(true);
  });

  it("should return isError based on mutation isError", () => {
    mockUseMutation.mockImplementation(() => ({
      mutate: mockMutate,
      isPending: false,
      isError: true,
      error: new Error("Test error"),
    }));

    const { result } = renderHook(() => useMoveWorkflowStep(workflowId));

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBeDefined();
  });

  describe("mutation callbacks", () => {
    it("should invalidate workflow query on success", () => {
      let capturedOnSuccess: (() => void) | undefined;
      mockUseMutation.mockImplementation((options: { onSuccess?: () => void }) => {
        capturedOnSuccess = options.onSuccess;
        return {
          mutate: mockMutate,
          isPending: false,
          isError: false,
          error: null,
        };
      });

      renderHook(() => useMoveWorkflowStep(workflowId));

      capturedOnSuccess?.();

      expect(queryClient.invalidateQueries).toHaveBeenCalled();
    });

    it("should call onSuccess option when provided", () => {
      const onSuccess = vi.fn();
      let capturedOnSuccess: (() => void) | undefined;
      mockUseMutation.mockImplementation((options: { onSuccess?: () => void }) => {
        capturedOnSuccess = options.onSuccess;
        return {
          mutate: mockMutate,
          isPending: false,
          isError: false,
          error: null,
        };
      });

      renderHook(() => useMoveWorkflowStep(workflowId, { onSuccess }));

      capturedOnSuccess?.();

      expect(onSuccess).toHaveBeenCalled();
    });

    it("should show error notification for 404 status", () => {
      let capturedOnError: ((error: { response?: { status: number } }) => void) | undefined;
      mockUseMutation.mockImplementation(
        (options: { onError?: (error: { response?: { status: number } }) => void }) => {
          capturedOnError = options.onError;
          return {
            mutate: mockMutate,
            isPending: false,
            isError: false,
            error: null,
          };
        },
      );

      renderHook(() => useMoveWorkflowStep(workflowId));

      capturedOnError?.({ response: { status: 404 } });

      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Not found",
          color: "red",
        }),
      );
    });

    it("should show error notification for 400 status", () => {
      let capturedOnError: ((error: { response?: { status: number } }) => void) | undefined;
      mockUseMutation.mockImplementation(
        (options: { onError?: (error: { response?: { status: number } }) => void }) => {
          capturedOnError = options.onError;
          return {
            mutate: mockMutate,
            isPending: false,
            isError: false,
            error: null,
          };
        },
      );

      renderHook(() => useMoveWorkflowStep(workflowId));

      capturedOnError?.({ response: { status: 400 } });

      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Cannot move step",
          color: "red",
        }),
      );
    });

    it("should show generic error notification for unexpected errors", () => {
      let capturedOnError: ((error: { response?: { status: number } }) => void) | undefined;
      mockUseMutation.mockImplementation(
        (options: { onError?: (error: { response?: { status: number } }) => void }) => {
          capturedOnError = options.onError;
          return {
            mutate: mockMutate,
            isPending: false,
            isError: false,
            error: null,
          };
        },
      );

      renderHook(() => useMoveWorkflowStep(workflowId));

      capturedOnError?.({ response: { status: 500 } });

      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Error moving step",
          color: "red",
        }),
      );
    });

    it("should call onError option when provided", () => {
      const onError = vi.fn();
      let capturedOnError: ((error: { response?: { status: number } }) => void) | undefined;
      mockUseMutation.mockImplementation(
        (options: { onError?: (error: { response?: { status: number } }) => void }) => {
          capturedOnError = options.onError;
          return {
            mutate: mockMutate,
            isPending: false,
            isError: false,
            error: null,
          };
        },
      );

      renderHook(() => useMoveWorkflowStep(workflowId, { onError }));

      const error = { response: { status: 500 } };
      capturedOnError?.(error);

      expect(onError).toHaveBeenCalledWith(error);
    });
  });
});
