import { MantineProvider } from "@mantine/core";
import type { StepResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { WorkflowStepCard, type WorkflowStepCardProps } from "../components/workflow-step-card";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowStepCard", () => {
  const mockStep: StepResponse = {
    id: "step-1",
    content: "Review the code changes",
    order: 1,
    createdAt: new Date("2024-01-01"),
    updatedAt: new Date("2024-01-01"),
  };

  const mockOnUpdate = vi.fn();
  const mockOnRemove = vi.fn();

  const defaultProps: WorkflowStepCardProps = {
    step: mockStep,
    stepNumber: 1,
    onUpdate: mockOnUpdate,
    onRemove: mockOnRemove,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render step number badge", () => {
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    expect(screen.getByText("Step 1")).toBeInTheDocument();
  });

  it("should render step content", () => {
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    expect(screen.getByText("Review the code changes")).toBeInTheDocument();
  });

  it("should render edit button", () => {
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /edit step/i })).toBeInTheDocument();
  });

  it("should render delete button", () => {
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /delete step/i })).toBeInTheDocument();
  });

  it("should switch to edit mode when edit button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit step/i }));

    expect(screen.getByLabelText(/step content/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/step content/i)).toHaveValue("Review the code changes");
  });

  it("should call onUpdate with correct values when editing is saved", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit step/i }));

    const textarea = screen.getByLabelText(/step content/i);
    await user.clear(textarea);
    await user.type(textarea, "Updated step content");

    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnUpdate).toHaveBeenCalledWith("step-1", { content: "Updated step content" });
    });
  });

  it("should exit edit mode when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit step/i }));
    await user.click(screen.getByRole("button", { name: /cancel/i }));

    expect(screen.queryByLabelText(/step content/i)).not.toBeInTheDocument();
    expect(screen.getByText("Review the code changes")).toBeInTheDocument();
  });

  it("should open delete confirmation modal when delete button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /delete step/i }));

    await waitFor(() => {
      expect(screen.getByText("Delete Step")).toBeInTheDocument();
      expect(
        screen.getByText(
          "Are you sure you want to delete this step? This action cannot be undone.",
        ),
      ).toBeInTheDocument();
    });
  });

  it("should call onRemove when delete is confirmed", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    // Open delete modal
    await user.click(screen.getByRole("button", { name: /delete step/i }));

    // Wait for modal to appear
    await waitFor(() => {
      expect(screen.getByText("Delete Step")).toBeInTheDocument();
    });

    // Confirm delete
    await user.click(screen.getByRole("button", { name: /^delete$/i }));

    await waitFor(() => {
      expect(mockOnRemove).toHaveBeenCalledWith("step-1");
    });
  });

  it("should close delete modal when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepCard {...defaultProps} />);

    // Open delete modal
    await user.click(screen.getByRole("button", { name: /delete step/i }));

    // Wait for modal to appear
    await waitFor(() => {
      expect(screen.getByText("Delete Step")).toBeInTheDocument();
    });

    // Cancel delete
    await user.click(screen.getByRole("button", { name: /^cancel$/i }));

    await waitFor(() => {
      expect(screen.queryByText("Delete Step")).not.toBeInTheDocument();
    });
  });

  it("should show loading state on delete button when isRemoving is true", () => {
    renderWithProviders(<WorkflowStepCard {...defaultProps} isRemoving={true} />);

    const deleteButton = screen.getByRole("button", { name: /delete step/i });
    expect(deleteButton).toHaveAttribute("data-loading", "true");
  });

  it("should preserve whitespace in content", () => {
    const stepWithWhitespace: StepResponse = {
      ...mockStep,
      content: "Line 1\nLine 2\nLine 3",
    };
    renderWithProviders(<WorkflowStepCard {...defaultProps} step={stepWithWhitespace} />);

    const contentElement = screen.getByText(/Line 1/);
    expect(contentElement).toHaveStyle({ whiteSpace: "pre-wrap" });
  });
});
