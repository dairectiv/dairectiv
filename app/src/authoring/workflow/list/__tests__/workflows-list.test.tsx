import { MantineProvider } from "@mantine/core";
import type { WorkflowResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { WorkflowsList, type WorkflowsListProps } from "../components/workflows-list";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowsList", () => {
  const mockWorkflows: WorkflowResponse[] = [
    {
      id: "1",
      name: "Code Review Workflow",
      description: "Steps for reviewing pull requests",
      state: "draft",
      createdAt: "2025-01-01T00:00:00Z",
      updatedAt: "2025-01-01T00:00:00Z",
      content: "",
      examples: [],
      steps: [],
    },
    {
      id: "2",
      name: "Deployment Workflow",
      description: "Production deployment process",
      state: "published",
      createdAt: "2025-01-01T00:00:00Z",
      updatedAt: "2025-01-01T00:00:00Z",
      content: "",
      examples: [],
      steps: [],
    },
  ];

  const defaultProps: WorkflowsListProps = {
    workflows: mockWorkflows,
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

  it("should render workflows list", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} />);

    expect(screen.getByText("Code Review Workflow")).toBeInTheDocument();
    expect(screen.getByText("Deployment Workflow")).toBeInTheDocument();
  });

  it("should render workflow descriptions", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} />);

    expect(screen.getByText("Steps for reviewing pull requests")).toBeInTheDocument();
    expect(screen.getByText("Production deployment process")).toBeInTheDocument();
  });

  it("should render state badges", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} />);

    // "Draft" and "Published" appear both in the badge and filter dropdown
    // Use getAllByText to verify they exist
    expect(screen.getAllByText("Draft").length).toBeGreaterThanOrEqual(1);
    expect(screen.getAllByText("Published").length).toBeGreaterThanOrEqual(1);
  });

  it("should render toolbar", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} />);

    expect(screen.getByPlaceholderText("Search workflows...")).toBeInTheDocument();
  });

  it("should show loading state", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} isLoading={true} workflows={[]} />);

    const loader = document.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should show error state", () => {
    const error = new Error("Network request failed");
    renderWithProviders(
      <WorkflowsList {...defaultProps} isError={true} error={error} workflows={[]} />,
    );

    // Title is always "Failed to load workflows", error message is displayed below
    expect(screen.getByText("Failed to load workflows")).toBeInTheDocument();
    expect(screen.getByText("Network request failed")).toBeInTheDocument();
  });

  it("should show empty state when no workflows", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} workflows={[]} />);

    expect(screen.getByText("No workflows found")).toBeInTheDocument();
  });

  it("should not show empty state when loading", () => {
    renderWithProviders(<WorkflowsList {...defaultProps} workflows={[]} isLoading={true} />);

    expect(screen.queryByText("No workflows found")).not.toBeInTheDocument();
  });

  it("should render pagination when multiple pages", () => {
    renderWithProviders(
      <WorkflowsList
        {...defaultProps}
        pagination={{ page: 1, limit: 10, total: 25, totalPages: 3 }}
      />,
    );

    expect(screen.getByText("Showing 2 of 25 workflows")).toBeInTheDocument();
  });

  it("should not render pagination when single page", () => {
    renderWithProviders(
      <WorkflowsList
        {...defaultProps}
        pagination={{ page: 1, limit: 10, total: 2, totalPages: 1 }}
      />,
    );

    expect(screen.queryByText(/Showing.*of.*workflows/)).not.toBeInTheDocument();
  });

  it("should show default error message when error has no message", () => {
    renderWithProviders(
      <WorkflowsList {...defaultProps} isError={true} error={null} workflows={[]} />,
    );

    // Only "Failed to load workflows" is shown when no error details available
    expect(screen.getByText("Failed to load workflows")).toBeInTheDocument();
  });
});
