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
  updateWorkflow: vi.fn(),
}));

// Mock query keys
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listWorkflowsQueryKey: () => ["workflows"],
  getWorkflowQueryKey: ({ path }: { path: { id: string } }) => ["workflow", path.id],
}));

import { useUpdateWorkflow } from "../hooks/use-update-workflow";

describe("useUpdateWorkflow", () => {
  const workflowId = "test-workflow-id";

  beforeEach(() => {
    vi.clearAllMocks();
    mockUseMutation.mockImplementation((options) => ({
      mutate: (variables: unknown) => {
        mockMutate(variables);
        // Simulate success by default
        options.onSuccess?.();
      },
      isPending: false,
      isError: false,
      error: null,
    }));
  });

  it("should call mutation with correct payload", async () => {
    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({
        name: "Updated Workflow",
        description: "Updated Description",
      });
    });

    expect(mockMutate).toHaveBeenCalledWith({
      name: "Updated Workflow",
      description: "Updated Description",
    });
  });

  it("should navigate to workflow detail page on success", async () => {
    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({
        name: "Updated Workflow",
        description: "Updated Description",
      });
    });

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/workflows/$workflowId",
        params: { workflowId },
      });
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({
        name: "Updated Workflow",
        description: "Updated Description",
      });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Workflow updated",
          color: "green",
        }),
      );
    });
  });

  it("should call onSuccess callback when provided", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useUpdateWorkflow(workflowId, { onSuccess }));

    act(() => {
      result.current.updateWorkflow({
        name: "Updated Workflow",
        description: "Updated Description",
      });
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

    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({ name: "Workflow", description: "Description" });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Workflow not found",
          color: "red",
        }),
      );
    });
  });

  it("should show conflict error notification for 409 status", async () => {
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

    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({ name: "Existing Workflow", description: "Description" });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Workflow already exists",
          color: "red",
        }),
      );
    });
  });

  it("should show validation error notification for 422 status", async () => {
    mockUseMutation.mockImplementation((options) => ({
      mutate: () => {
        const error = {
          response: { status: 422 },
        } as AxiosError;
        options.onError?.(error);
      },
      isPending: false,
      isError: true,
      error: null,
    }));

    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({ name: "", description: "" });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Validation error",
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

    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    act(() => {
      result.current.updateWorkflow({ name: "Test", description: "Test" });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Error updating workflow",
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

    const { result } = renderHook(() => useUpdateWorkflow(workflowId, { onError }));

    act(() => {
      result.current.updateWorkflow({ name: "Test", description: "Test" });
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

    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    expect(result.current.isUpdating).toBe(true);
  });

  it("should return error state", () => {
    const error = new Error("Test error");
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useUpdateWorkflow(workflowId));

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });
});
