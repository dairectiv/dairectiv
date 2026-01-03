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
  addWorkflowExample: vi.fn(),
}));

vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getWorkflowQueryKey: vi.fn(() => ["getWorkflow", "test-workflow-id"]),
}));

import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { useAddWorkflowExample } from "../hooks/use-add-workflow-example";

describe("useAddWorkflowExample", () => {
  const workflowId = "test-workflow-id";

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

  it("should return addExample function", () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    expect(result.current.addExample).toBeDefined();
    expect(typeof result.current.addExample).toBe("function");
  });

  it("should return isAdding state", () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    expect(result.current.isAdding).toBe(false);
  });

  it("should return isError state", () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    expect(result.current.isError).toBe(false);
  });

  it("should call mutate when addExample is called", () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    expect(mockMutate).toHaveBeenCalledWith({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });
  });

  it("should pass onSuccess callback to hook options", () => {
    const onSuccess = vi.fn();
    renderHook(() => useAddWorkflowExample(workflowId, { onSuccess }));

    const mutationOptions = mockUseMutation.mock.calls[0][0];
    expect(mutationOptions.onSuccess).toBeDefined();
  });

  it("should invalidate query cache on success", async () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(queryClient.invalidateQueries).toHaveBeenCalled();
    });
  });

  it("should show loading notification on mutate", async () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(mockShowLoadingNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Adding example",
          loadingMessage: "Adding example to workflow...",
        }),
      );
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(mockUpdateToSuccess).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Example added",
        }),
      );
    });
  });

  it("should call user onSuccess callback on success", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useAddWorkflowExample(workflowId, { onSuccess }));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
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

    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
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

    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Cannot add example",
        }),
      );
    });
  });

  it("should show error notification on 422 error", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const context = options.onMutate?.();
        const error = new Error("Validation error") as Error & { response?: { status: number } };
        error.response = { status: 422 };
        options.onError?.(error, undefined, context);
      },
      isPending: false,
      isError: false,
      error: null,
    }));

    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Validation error",
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

    const { result } = renderHook(() => useAddWorkflowExample(workflowId));

    result.current.addExample({
      scenario: "Test scenario",
      input: "Test input",
      output: "Test output",
    });

    await waitFor(() => {
      expect(mockUpdateToError).toHaveBeenCalledWith(
        "test-notification-id",
        expect.objectContaining({
          title: "Error adding example",
        }),
      );
    });
  });
});
