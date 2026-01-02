import { renderHook, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock TanStack Router
const mockNavigate = vi.fn();
vi.mock("@tanstack/react-router", () => ({
  useNavigate: () => mockNavigate,
  useSearch: () => ({ page: 1 }),
}));

// Mock TanStack Query
const mockUseQuery = vi.fn();
vi.mock("@tanstack/react-query", () => ({
  useQuery: (options: unknown) => mockUseQuery(options),
}));

// Mock API options
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  listWorkflowsOptions: (opts: unknown) => ({ queryKey: ["workflows", opts], queryFn: vi.fn() }),
}));

import { useWorkflowsList } from "../hooks/use-workflows-list";

describe("useWorkflowsList", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockUseQuery.mockReturnValue({
      data: {
        items: [
          { id: "1", name: "Workflow 1", description: "Description 1", state: "draft" },
          { id: "2", name: "Workflow 2", description: "Description 2", state: "published" },
        ],
        pagination: { page: 1, limit: 10, total: 2, totalPages: 1 },
      },
      isLoading: false,
      isError: false,
      error: null,
    });
  });

  it("should return workflows from the query", () => {
    const { result } = renderHook(() => useWorkflowsList());

    expect(result.current.workflows).toHaveLength(2);
    expect(result.current.workflows[0].name).toBe("Workflow 1");
  });

  it("should return pagination data", () => {
    const { result } = renderHook(() => useWorkflowsList());

    expect(result.current.pagination).toEqual({
      page: 1,
      limit: 10,
      total: 2,
      totalPages: 1,
    });
  });

  it("should return loading state", () => {
    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
    });

    const { result } = renderHook(() => useWorkflowsList());

    expect(result.current.isLoading).toBe(true);
    expect(result.current.workflows).toEqual([]);
  });

  it("should return error state", () => {
    const error = new Error("Failed to fetch");
    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useWorkflowsList());

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });

  it("should navigate when setPage is called", async () => {
    const { result } = renderHook(() => useWorkflowsList());

    result.current.setPage(2);

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/workflows",
        search: { page: 2 },
      });
    });
  });

  it("should reset page when setSearch is called", async () => {
    const { result } = renderHook(() => useWorkflowsList());

    result.current.setSearch("test");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/workflows",
        search: expect.objectContaining({ search: "test", page: 1 }),
      });
    });
  });

  it("should reset page when setState is called", async () => {
    const { result } = renderHook(() => useWorkflowsList());

    result.current.setState("published");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/workflows",
        search: expect.objectContaining({ state: "published", page: 1 }),
      });
    });
  });

  it("should update sort when setSort is called", async () => {
    const { result } = renderHook(() => useWorkflowsList());

    result.current.setSort("name", "asc");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/workflows",
        search: expect.objectContaining({ sortBy: "name", sortOrder: "asc" }),
      });
    });
  });

  it("should clear search when empty string is passed", async () => {
    const { result } = renderHook(() => useWorkflowsList());

    result.current.setSearch("");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/workflows",
        search: expect.objectContaining({ search: undefined, page: 1 }),
      });
    });
  });
});
