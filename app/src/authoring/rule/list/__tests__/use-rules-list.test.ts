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
  listRulesOptions: (opts: unknown) => ({ queryKey: ["rules", opts], queryFn: vi.fn() }),
}));

import { useRulesList } from "../hooks/use-rules-list";

describe("useRulesList", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockUseQuery.mockReturnValue({
      data: {
        items: [
          { id: "1", name: "Rule 1", description: "Description 1", state: "draft" },
          { id: "2", name: "Rule 2", description: "Description 2", state: "published" },
        ],
        pagination: { page: 1, limit: 10, total: 2, totalPages: 1 },
      },
      isLoading: false,
      isError: false,
      error: null,
    });
  });

  it("should return rules from the query", () => {
    const { result } = renderHook(() => useRulesList());

    expect(result.current.rules).toHaveLength(2);
    expect(result.current.rules[0].name).toBe("Rule 1");
  });

  it("should return pagination data", () => {
    const { result } = renderHook(() => useRulesList());

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

    const { result } = renderHook(() => useRulesList());

    expect(result.current.isLoading).toBe(true);
    expect(result.current.rules).toEqual([]);
  });

  it("should return error state", () => {
    const error = new Error("Failed to fetch");
    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useRulesList());

    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });

  it("should navigate when setPage is called", async () => {
    const { result } = renderHook(() => useRulesList());

    result.current.setPage(2);

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/rules",
        search: { page: 2 },
      });
    });
  });

  it("should reset page when setSearch is called", async () => {
    const { result } = renderHook(() => useRulesList());

    result.current.setSearch("test");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/rules",
        search: expect.objectContaining({ search: "test", page: 1 }),
      });
    });
  });

  it("should reset page when setState is called", async () => {
    const { result } = renderHook(() => useRulesList());

    result.current.setState("published");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/rules",
        search: expect.objectContaining({ state: "published", page: 1 }),
      });
    });
  });

  it("should update sort when setSort is called", async () => {
    const { result } = renderHook(() => useRulesList());

    result.current.setSort("name", "asc");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/rules",
        search: expect.objectContaining({ sortBy: "name", sortOrder: "asc" }),
      });
    });
  });

  it("should clear search when empty string is passed", async () => {
    const { result } = renderHook(() => useRulesList());

    result.current.setSearch("");

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith({
        to: "/authoring/rules",
        search: expect.objectContaining({ search: undefined, page: 1 }),
      });
    });
  });
});
