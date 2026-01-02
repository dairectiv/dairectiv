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
  draftWorkflow: vi.fn(),
}));

// Mock query key
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listWorkflowsQueryKey: () => ["workflows"],
}));

import { useDraftWorkflow } from "../hooks/use-draft-workflow";

describe("useDraftWorkflow", () => {
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
    const { result } = renderHook(() => useDraftWorkflow());

    act(() => {
      result.current.draftWorkflow({ name: "Test Workflow", description: "Test Description" });
    });

    expect(mockMutate).toHaveBeenCalledWith({
      name: "Test Workflow",
      description: "Test Description",
    });
  });

  it("should navigate to workflows list on success", async () => {
    const { result } = renderHook(() => useDraftWorkflow());

    act(() => {
      result.current.draftWorkflow({ name: "Test Workflow", description: "Test Description" });
    });

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({ to: "/authoring/workflows" });
    });
  });

  it("should show success notification on success", async () => {
    const { result } = renderHook(() => useDraftWorkflow());

    act(() => {
      result.current.draftWorkflow({ name: "Test Workflow", description: "Test Description" });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Workflow created",
          color: "green",
        }),
      );
    });
  });

  it("should call onSuccess callback when provided", async () => {
    const onSuccess = vi.fn();
    const { result } = renderHook(() => useDraftWorkflow({ onSuccess }));

    act(() => {
      result.current.draftWorkflow({ name: "Test Workflow", description: "Test Description" });
    });

    await waitFor(() => {
      expect(onSuccess).toHaveBeenCalled();
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

    const { result } = renderHook(() => useDraftWorkflow());

    act(() => {
      result.current.draftWorkflow({ name: "Existing Workflow", description: "Description" });
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

    const { result } = renderHook(() => useDraftWorkflow());

    act(() => {
      result.current.draftWorkflow({ name: "", description: "" });
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

    const { result } = renderHook(() => useDraftWorkflow());

    act(() => {
      result.current.draftWorkflow({ name: "Test", description: "Test" });
    });

    await waitFor(() => {
      expect(mockShowNotification).toHaveBeenCalledWith(
        expect.objectContaining({
          title: "Error creating workflow",
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

    const { result } = renderHook(() => useDraftWorkflow({ onError }));

    act(() => {
      result.current.draftWorkflow({ name: "Test", description: "Test" });
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

    const { result } = renderHook(() => useDraftWorkflow());

    expect(result.current.isLoading).toBe(true);
  });

  it("should return error state", () => {
    const error = new Error("Test error");
    mockUseMutation.mockReturnValue({
      mutate: mockMutate,
      isPending: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useDraftWorkflow());

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });
});
