import { renderHook } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock TanStack Query
const mockUseQuery = vi.fn();
vi.mock("@tanstack/react-query", () => ({
  useQuery: (options: unknown) => mockUseQuery(options),
}));

// Mock API options
vi.mock("@shared/infrastructure/api/generated/@tanstack/react-query.gen", () => ({
  getRuleOptions: (opts: { path: { id: string } }) => ({
    queryKey: ["rule", opts.path.id],
    queryFn: vi.fn(),
  }),
}));

import { useRuleDetail } from "../hooks/use-rule-detail";

describe("useRuleDetail", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should return rule data when query succeeds", () => {
    const mockRule = {
      id: "rule-1",
      name: "Test Rule",
      description: "Test Description",
      state: "draft",
      content: null,
      examples: [],
      createdAt: "2025-01-01T00:00:00Z",
      updatedAt: "2025-01-01T00:00:00Z",
    };

    mockUseQuery.mockReturnValue({
      data: mockRule,
      isLoading: false,
      isError: false,
      error: null,
    });

    const { result } = renderHook(() => useRuleDetail("rule-1"));

    expect(result.current.rule).toEqual(mockRule);
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

    const { result } = renderHook(() => useRuleDetail("rule-1"));

    expect(result.current.rule).toBeUndefined();
    expect(result.current.isLoading).toBe(true);
    expect(result.current.isError).toBe(false);
  });

  it("should return error state", () => {
    const error = new Error("Failed to fetch rule");

    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error,
    });

    const { result } = renderHook(() => useRuleDetail("rule-1"));

    expect(result.current.rule).toBeUndefined();
    expect(result.current.isLoading).toBe(false);
    expect(result.current.isError).toBe(true);
    expect(result.current.error).toBe(error);
  });

  it("should pass rule id to query options", () => {
    mockUseQuery.mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
    });

    renderHook(() => useRuleDetail("my-rule-id"));

    expect(mockUseQuery).toHaveBeenCalledWith(
      expect.objectContaining({
        queryKey: ["rule", "my-rule-id"],
      }),
    );
  });
});
