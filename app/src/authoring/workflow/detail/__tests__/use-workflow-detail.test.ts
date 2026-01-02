import { renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock TanStack Query
const mockUseQuery = vi.fn();
vi.mock("@tanstack/react-query", () => ({
  useQuery: (options: unknown) => mockUseQuery(options),
}));

// Mock API options
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getWorkflowOptions: (opts: { path: { id: string } }) => ({
    queryKey: ["workflow", opts.path.id],
    queryFn: vi.fn(),
  }),
}));

import { useWorkflowDetail } from "../hooks/use-workflow-detail";

describe("useWorkflowDetail", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should return workflow data when query succeeds", () => {
    const mockWorkflow = {
      id: "workflow-1",
      name: "Test Workflow",
      description: "Test Description",
      state: "draft",
      content: null,
      steps: [],
      examples: [],
      createdAt: "2025-01-01T00:00:00Z",
      updatedAt: "2025-01-01T00:00:00Z",
    };

    mockUseQuery.mockReturnValue({
      data: mockWorkflow,
      isLoading: false,
      isError: false,
      error: null,
    });

    const { result } = renderHook(() => useWorkflowDetail("workflow-1"));

    expect(result.current.workflow).toEqual(mockWorkflow);
    expect(result.current.isLoading).toBe(false);
    expect(result.current.isError).toBe(false);
    expect(result.current.error).toBeNull();
  });

  it("should return loading state", () => {
    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
    });

    const { result } = renderHook(() => useWorkflowDetail("workflow-1"));

    expect(result.current.workflow).toBeUndefined();
    expect(result.current.isLoading).toBe(true);
    expect(result.current.isError).toBe(false);
  });

  it("should return error state", () => {
    const error = new Error("Failed to fetch workflow");

    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useWorkflowDetail("workflow-1"));

    expect(result.current.workflow).toBeUndefined();
    expect(result.current.isLoading).toBe(false);
    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });

  it("should pass workflow id to query options", () => {
    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
    });

    renderHook(() => useWorkflowDetail("my-workflow-id"));

    expect(mockUseQuery).toHaveBeenCalledWith(
      expect.objectContaining({
        queryKey: ["workflow", "my-workflow-id"],
      }),
    );
  });
});
