import { MantineProvider } from "@mantine/core";
import type { WorkflowResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { WorkflowDetail, type WorkflowDetailProps } from "../components/workflow-detail";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowDetail", () => {
  const mockWorkflow: WorkflowResponse = {
    id: "workflow-1",
    name: "Code Review Workflow",
    description: "Steps for reviewing pull requests",
    state: "draft",
    content: "This is the workflow content",
    createdAt: new Date("2025-01-01T00:00:00Z"),
    updatedAt: new Date("2025-01-01T00:00:00Z"),
    steps: [
      {
        id: "step-1",
        order: 1,
        content: "Review the code changes",
        createdAt: new Date("2025-01-01T00:00:00Z"),
        updatedAt: new Date("2025-01-01T00:00:00Z"),
      },
      {
        id: "step-2",
        order: 2,
        content: "Check for tests",
        createdAt: new Date("2025-01-01T00:00:00Z"),
        updatedAt: new Date("2025-01-01T00:00:00Z"),
      },
    ],
    examples: [
      {
        id: "example-1",
        scenario: "PR with bug fix",
        input: "A pull request fixing a null pointer exception",
        output: "Approved after verifying the fix and test coverage",
        explanation: "Ensure the fix is properly tested",
        createdAt: new Date("2025-01-01T00:00:00Z"),
        updatedAt: new Date("2025-01-01T00:00:00Z"),
      },
    ],
  };

  const defaultProps: WorkflowDetailProps = {
    workflow: mockWorkflow,
    isLoading: false,
    isError: false,
    error: null,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render workflow name and description", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByText("Code Review Workflow")).toBeInTheDocument();
    expect(screen.getByText("Steps for reviewing pull requests")).toBeInTheDocument();
  });

  it("should render workflow state badge", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByText("Draft")).toBeInTheDocument();
  });

  it("should render published badge for published workflow", () => {
    renderWithProviders(
      <WorkflowDetail {...defaultProps} workflow={{ ...mockWorkflow, state: "published" }} />,
    );

    expect(screen.getByText("Published")).toBeInTheDocument();
  });

  it("should render workflow content when present", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByText("Content")).toBeInTheDocument();
    expect(screen.getByText("This is the workflow content")).toBeInTheDocument();
  });

  it("should not render content section when content is null", () => {
    renderWithProviders(
      <WorkflowDetail {...defaultProps} workflow={{ ...mockWorkflow, content: null }} />,
    );

    expect(screen.queryByText("Content")).not.toBeInTheDocument();
  });

  it("should render steps section with step count", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByText("Steps (2)")).toBeInTheDocument();
    expect(screen.getByText("Step 1")).toBeInTheDocument();
    expect(screen.getByText("Step 2")).toBeInTheDocument();
    expect(screen.getByText("Review the code changes")).toBeInTheDocument();
    expect(screen.getByText("Check for tests")).toBeInTheDocument();
  });

  it("should render empty state for steps when no steps exist", () => {
    renderWithProviders(
      <WorkflowDetail {...defaultProps} workflow={{ ...mockWorkflow, steps: [] }} />,
    );

    expect(screen.getByText("Steps (0)")).toBeInTheDocument();
    expect(
      screen.getByText("No steps defined yet. Add steps to guide the workflow execution."),
    ).toBeInTheDocument();
  });

  it("should render examples section with example count", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByText("Examples (1)")).toBeInTheDocument();
    expect(screen.getByText("PR with bug fix")).toBeInTheDocument();
    expect(screen.getByText("A pull request fixing a null pointer exception")).toBeInTheDocument();
    expect(
      screen.getByText("Approved after verifying the fix and test coverage"),
    ).toBeInTheDocument();
  });

  it("should render example explanation when present", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByText("Ensure the fix is properly tested")).toBeInTheDocument();
  });

  it("should render empty state for examples when no examples exist", () => {
    renderWithProviders(
      <WorkflowDetail {...defaultProps} workflow={{ ...mockWorkflow, examples: [] }} />,
    );

    expect(screen.getByText("Examples (0)")).toBeInTheDocument();
    expect(
      screen.getByText(
        "No examples defined yet. Add examples to demonstrate the workflow in action.",
      ),
    ).toBeInTheDocument();
  });

  it("should render edit button for draft workflow", () => {
    renderWithProviders(<WorkflowDetail {...defaultProps} />);

    expect(screen.getByRole("link", { name: /edit/i })).toBeInTheDocument();
  });

  it("should not render edit button for published workflow", () => {
    renderWithProviders(
      <WorkflowDetail {...defaultProps} workflow={{ ...mockWorkflow, state: "published" }} />,
    );

    expect(screen.queryByRole("link", { name: /edit/i })).not.toBeInTheDocument();
  });

  it("should not render edit button for archived workflow", () => {
    renderWithProviders(
      <WorkflowDetail {...defaultProps} workflow={{ ...mockWorkflow, state: "archived" }} />,
    );

    expect(screen.queryByRole("link", { name: /edit/i })).not.toBeInTheDocument();
  });

  it("should show loading state", () => {
    renderWithProviders(<WorkflowDetail workflow={undefined} isLoading={true} isError={false} />);

    const loader = document.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should show error state with message", () => {
    const error = new Error("Network request failed");
    renderWithProviders(
      <WorkflowDetail workflow={undefined} isLoading={false} isError={true} error={error} />,
    );

    expect(screen.getByText("Error loading workflow")).toBeInTheDocument();
    expect(screen.getByText("Network request failed")).toBeInTheDocument();
  });

  it("should show error state with default message when no error message", () => {
    renderWithProviders(
      <WorkflowDetail workflow={undefined} isLoading={false} isError={true} error={null} />,
    );

    expect(screen.getByText("Error loading workflow")).toBeInTheDocument();
    expect(screen.getByText("An unexpected error occurred")).toBeInTheDocument();
  });

  it("should show not found state when workflow is undefined and not loading or error", () => {
    renderWithProviders(<WorkflowDetail workflow={undefined} isLoading={false} isError={false} />);

    expect(screen.getByText("Workflow not found")).toBeInTheDocument();
    expect(screen.getByText("The requested workflow could not be found.")).toBeInTheDocument();
  });

  it("should render steps in correct order", () => {
    const workflowWithUnorderedSteps: WorkflowResponse = {
      ...mockWorkflow,
      steps: [
        {
          id: "step-3",
          order: 3,
          content: "Third step",
          createdAt: new Date("2025-01-01T00:00:00Z"),
          updatedAt: new Date("2025-01-01T00:00:00Z"),
        },
        {
          id: "step-1",
          order: 1,
          content: "First step",
          createdAt: new Date("2025-01-01T00:00:00Z"),
          updatedAt: new Date("2025-01-01T00:00:00Z"),
        },
        {
          id: "step-2",
          order: 2,
          content: "Second step",
          createdAt: new Date("2025-01-01T00:00:00Z"),
          updatedAt: new Date("2025-01-01T00:00:00Z"),
        },
      ],
    };

    renderWithProviders(<WorkflowDetail {...defaultProps} workflow={workflowWithUnorderedSteps} />);

    const steps = screen.getAllByText(/^Step \d$/);
    expect(steps[0]).toHaveTextContent("Step 1");
    expect(steps[1]).toHaveTextContent("Step 2");
    expect(steps[2]).toHaveTextContent("Step 3");
  });
});
