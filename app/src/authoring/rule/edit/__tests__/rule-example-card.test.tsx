import { MantineProvider } from "@mantine/core";
import type { RuleExampleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { RuleExampleCard, type RuleExampleCardProps } from "../components/rule-example-card";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RuleExampleCard", () => {
  const mockExample: RuleExampleResponse = {
    id: "example-1",
    createdAt: new Date("2024-01-01"),
    updatedAt: new Date("2024-01-01"),
    good: "Use const for variables that do not change",
    bad: "Use var for all variables",
    explanation: "Const prevents accidental reassignment",
  };

  const defaultProps: RuleExampleCardProps = {
    example: mockExample,
    onUpdate: vi.fn(),
    onRemove: vi.fn(),
    isUpdating: false,
    isRemoving: false,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render example content in view mode", () => {
    renderWithProviders(<RuleExampleCard {...defaultProps} />);

    expect(screen.getByText("Good")).toBeInTheDocument();
    expect(screen.getByText("Use const for variables that do not change")).toBeInTheDocument();
    expect(screen.getByText("Bad")).toBeInTheDocument();
    expect(screen.getByText("Use var for all variables")).toBeInTheDocument();
    expect(screen.getByText("Const prevents accidental reassignment")).toBeInTheDocument();
  });

  it("should render edit and delete buttons", () => {
    renderWithProviders(<RuleExampleCard {...defaultProps} />);

    expect(screen.getByLabelText(/edit example/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/delete example/i)).toBeInTheDocument();
  });

  it("should switch to edit mode when edit button is clicked", () => {
    renderWithProviders(<RuleExampleCard {...defaultProps} />);

    fireEvent.click(screen.getByLabelText(/edit example/i));

    // Form should be visible with current values
    expect(screen.getByLabelText(/good example/i)).toHaveValue(
      "Use const for variables that do not change",
    );
    expect(screen.getByLabelText(/bad example/i)).toHaveValue("Use var for all variables");
    expect(screen.getByLabelText(/explanation/i)).toHaveValue(
      "Const prevents accidental reassignment",
    );
  });

  it("should call onUpdate when form is submitted in edit mode", async () => {
    const onUpdate = vi.fn();
    renderWithProviders(<RuleExampleCard {...defaultProps} onUpdate={onUpdate} />);

    // Enter edit mode
    fireEvent.click(screen.getByLabelText(/edit example/i));

    // Modify values
    fireEvent.change(screen.getByLabelText(/good example/i), {
      target: { value: "Updated good example" },
    });
    fireEvent.click(screen.getByRole("button", { name: /update/i }));

    await waitFor(() => {
      expect(onUpdate).toHaveBeenCalledWith("example-1", {
        good: "Updated good example",
        bad: "Use var for all variables",
        explanation: "Const prevents accidental reassignment",
      });
    });
  });

  it("should exit edit mode when cancel is clicked", async () => {
    renderWithProviders(<RuleExampleCard {...defaultProps} />);

    // Enter edit mode
    fireEvent.click(screen.getByLabelText(/edit example/i));

    // Cancel
    fireEvent.click(screen.getByRole("button", { name: /cancel/i }));

    // Should be back in view mode
    await waitFor(() => {
      expect(screen.getByText("Good")).toBeInTheDocument();
      expect(screen.getByLabelText(/edit example/i)).toBeInTheDocument();
    });
  });

  it("should open delete confirmation modal when delete button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<RuleExampleCard {...defaultProps} />);

    await user.click(screen.getByLabelText(/delete example/i));

    await waitFor(() => {
      expect(screen.getByText("Remove Example")).toBeInTheDocument();
      expect(screen.getByText(/are you sure you want to remove this example/i)).toBeInTheDocument();
    });
  });

  it("should call onRemove when delete is confirmed", async () => {
    const user = userEvent.setup();
    const onRemove = vi.fn();
    renderWithProviders(<RuleExampleCard {...defaultProps} onRemove={onRemove} />);

    // Open delete modal
    await user.click(screen.getByLabelText(/delete example/i));

    // Wait for modal to appear
    await waitFor(() => {
      expect(screen.getByText("Remove Example")).toBeInTheDocument();
    });

    // Confirm delete
    await user.click(screen.getByRole("button", { name: /^remove$/i }));

    await waitFor(() => {
      expect(onRemove).toHaveBeenCalledWith("example-1");
    });
  });

  it("should close delete modal when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<RuleExampleCard {...defaultProps} />);

    // Open delete modal
    await user.click(screen.getByLabelText(/delete example/i));

    // Wait for modal to appear
    await waitFor(() => {
      expect(screen.getByText("Remove Example")).toBeInTheDocument();
    });

    // Cancel
    await user.click(screen.getByRole("button", { name: "Cancel" }));

    await waitFor(() => {
      expect(screen.queryByText("Remove Example")).not.toBeInTheDocument();
    });
  });

  it("should disable edit button when removing", () => {
    renderWithProviders(<RuleExampleCard {...defaultProps} isRemoving={true} />);

    expect(screen.getByLabelText(/edit example/i)).toBeDisabled();
  });

  it("should show loading state on delete button when removing", () => {
    renderWithProviders(<RuleExampleCard {...defaultProps} isRemoving={true} />);

    const deleteButton = screen.getByLabelText(/delete example/i);
    const loader = deleteButton.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should handle example without explanation", () => {
    const exampleWithoutExplanation: RuleExampleResponse = {
      ...mockExample,
      explanation: null,
    };

    renderWithProviders(<RuleExampleCard {...defaultProps} example={exampleWithoutExplanation} />);

    expect(screen.getByText("Good")).toBeInTheDocument();
    expect(screen.getByText("Bad")).toBeInTheDocument();
    expect(screen.queryByText("Const prevents accidental reassignment")).not.toBeInTheDocument();
  });

  it("should handle example with null good and bad values", () => {
    const partialExample: RuleExampleResponse = {
      ...mockExample,
      good: null,
      bad: null,
    };

    renderWithProviders(<RuleExampleCard {...defaultProps} example={partialExample} />);

    expect(screen.queryByText("Good")).not.toBeInTheDocument();
    expect(screen.queryByText("Bad")).not.toBeInTheDocument();
    expect(screen.getByText("Const prevents accidental reassignment")).toBeInTheDocument();
  });
});
