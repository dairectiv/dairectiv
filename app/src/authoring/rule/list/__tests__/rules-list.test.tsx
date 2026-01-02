import { MantineProvider } from "@mantine/core";
import type { RuleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { RulesList, type RulesListProps } from "../components/rules-list";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RulesList", () => {
  const mockRules: RuleResponse[] = [
    {
      id: "1",
      name: "Authentication Rule",
      description: "Rules for user authentication",
      state: "draft",
      createdAt: "2025-01-01T00:00:00Z",
      updatedAt: "2025-01-01T00:00:00Z",
      content: "",
      examples: [],
    },
    {
      id: "2",
      name: "Validation Rule",
      description: "Input validation patterns",
      state: "published",
      createdAt: "2025-01-01T00:00:00Z",
      updatedAt: "2025-01-01T00:00:00Z",
      content: "",
      examples: [],
    },
  ];

  const defaultProps: RulesListProps = {
    rules: mockRules,
    pagination: { page: 1, limit: 10, total: 2, totalPages: 1 },
    filters: {},
    isLoading: false,
    isError: false,
    error: null,
    onPageChange: vi.fn(),
    onSearchChange: vi.fn(),
    onStateChange: vi.fn(),
    onSortChange: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render rules list", () => {
    renderWithProviders(<RulesList {...defaultProps} />);

    expect(screen.getByText("Authentication Rule")).toBeInTheDocument();
    expect(screen.getByText("Validation Rule")).toBeInTheDocument();
  });

  it("should render rule descriptions", () => {
    renderWithProviders(<RulesList {...defaultProps} />);

    expect(screen.getByText("Rules for user authentication")).toBeInTheDocument();
    expect(screen.getByText("Input validation patterns")).toBeInTheDocument();
  });

  it("should render state badges", () => {
    renderWithProviders(<RulesList {...defaultProps} />);

    // "Draft" and "Published" appear both in the badge and filter dropdown
    // Use getAllByText to verify they exist
    expect(screen.getAllByText("Draft").length).toBeGreaterThanOrEqual(1);
    expect(screen.getAllByText("Published").length).toBeGreaterThanOrEqual(1);
  });

  it("should render toolbar", () => {
    renderWithProviders(<RulesList {...defaultProps} />);

    expect(screen.getByPlaceholderText("Search rules...")).toBeInTheDocument();
  });

  it("should show loading state", () => {
    renderWithProviders(<RulesList {...defaultProps} isLoading={true} rules={[]} />);

    const loader = document.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should show error state", () => {
    const error = new Error("Network request failed");
    renderWithProviders(<RulesList {...defaultProps} isError={true} error={error} rules={[]} />);

    // Title is always "Failed to load rules", error message is displayed below
    expect(screen.getByText("Failed to load rules")).toBeInTheDocument();
    expect(screen.getByText("Network request failed")).toBeInTheDocument();
  });

  it("should show empty state when no rules", () => {
    renderWithProviders(<RulesList {...defaultProps} rules={[]} />);

    expect(screen.getByText("No rules found")).toBeInTheDocument();
  });

  it("should not show empty state when loading", () => {
    renderWithProviders(<RulesList {...defaultProps} rules={[]} isLoading={true} />);

    expect(screen.queryByText("No rules found")).not.toBeInTheDocument();
  });

  it("should render pagination when multiple pages", () => {
    renderWithProviders(
      <RulesList {...defaultProps} pagination={{ page: 1, limit: 10, total: 25, totalPages: 3 }} />,
    );

    expect(screen.getByText("Showing 2 of 25 rules")).toBeInTheDocument();
  });

  it("should not render pagination when single page", () => {
    renderWithProviders(
      <RulesList {...defaultProps} pagination={{ page: 1, limit: 10, total: 2, totalPages: 1 }} />,
    );

    expect(screen.queryByText(/Showing.*of.*rules/)).not.toBeInTheDocument();
  });

  it("should show default error message when error has no message", () => {
    renderWithProviders(<RulesList {...defaultProps} isError={true} error={null} rules={[]} />);

    // Only "Failed to load rules" is shown when no error details available
    expect(screen.getByText("Failed to load rules")).toBeInTheDocument();
  });
});
