import { MantineProvider } from "@mantine/core";
import type { WorkflowExampleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import {
  WorkflowExampleCard,
  type WorkflowExampleCardProps,
} from "../components/workflow-example-card";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowExampleCard", () => {
  const mockExample: WorkflowExampleResponse = {
    id: "example-1",
    scenario: "User wants to generate a commit message",
    input: "git diff showing file changes",
    output: "feat(auth): add login functionality",
    explanation: "The commit message follows conventional commits format",
    createdAt: new Date("2024-01-01"),
    updatedAt: new Date("2024-01-01"),
  };

  const mockOnUpdate = vi.fn();
  const mockOnRemove = vi.fn();

  const defaultProps: WorkflowExampleCardProps = {
    example: mockExample,
    exampleNumber: 1,
    onUpdate: mockOnUpdate,
    onRemove: mockOnRemove,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render example number badge", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(screen.getByText("Example 1")).toBeInTheDocument();
  });

  it("should render scenario content", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(screen.getByText("User wants to generate a commit message")).toBeInTheDocument();
  });

  it("should render input content", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(screen.getByText("git diff showing file changes")).toBeInTheDocument();
  });

  it("should render output content", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(screen.getByText("feat(auth): add login functionality")).toBeInTheDocument();
  });

  it("should render explanation when provided", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(
      screen.getByText("The commit message follows conventional commits format"),
    ).toBeInTheDocument();
  });

  it("should not render explanation section when not provided", () => {
    const exampleWithoutExplanation = { ...mockExample, explanation: undefined };
    renderWithProviders(
      <WorkflowExampleCard {...defaultProps} example={exampleWithoutExplanation} />,
    );

    expect(screen.queryByText("Explanation")).not.toBeInTheDocument();
  });

  it("should render edit button", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /edit example/i })).toBeInTheDocument();
  });

  it("should render delete button", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /delete example/i })).toBeInTheDocument();
  });

  it("should switch to edit mode when edit button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit example/i }));

    expect(screen.getByLabelText(/scenario/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/scenario/i)).toHaveValue(
      "User wants to generate a commit message",
    );
  });

  it("should call onUpdate with correct values when editing is saved", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit example/i }));

    const scenarioTextarea = screen.getByLabelText(/scenario/i);
    await user.clear(scenarioTextarea);
    await user.type(scenarioTextarea, "Updated scenario");

    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnUpdate).toHaveBeenCalledWith(
        "example-1",
        expect.objectContaining({
          scenario: "Updated scenario",
        }),
      );
    });
  });

  it("should exit edit mode when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit example/i }));
    await user.click(screen.getByRole("button", { name: /cancel/i }));

    expect(screen.queryByLabelText(/scenario/i)).not.toBeInTheDocument();
    expect(screen.getByText("User wants to generate a commit message")).toBeInTheDocument();
  });

  it("should open delete confirmation modal when delete button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /delete example/i }));

    await waitFor(() => {
      expect(screen.getByText("Delete Example")).toBeInTheDocument();
      expect(
        screen.getByText(
          "Are you sure you want to delete this example? This action cannot be undone.",
        ),
      ).toBeInTheDocument();
    });
  });

  it("should call onRemove when delete is confirmed", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    // Open delete modal
    await user.click(screen.getByRole("button", { name: /delete example/i }));

    // Wait for modal to appear
    await waitFor(() => {
      expect(screen.getByText("Delete Example")).toBeInTheDocument();
    });

    // Confirm delete
    await user.click(screen.getByRole("button", { name: /^delete$/i }));

    await waitFor(() => {
      expect(mockOnRemove).toHaveBeenCalledWith("example-1");
    });
  });

  it("should close delete modal when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleCard {...defaultProps} />);

    // Open delete modal
    await user.click(screen.getByRole("button", { name: /delete example/i }));

    // Wait for modal to appear
    await waitFor(() => {
      expect(screen.getByText("Delete Example")).toBeInTheDocument();
    });

    // Cancel delete
    await user.click(screen.getByRole("button", { name: /^cancel$/i }));

    await waitFor(() => {
      expect(screen.queryByText("Delete Example")).not.toBeInTheDocument();
    });
  });

  it("should show loading state on delete button when isRemoving is true", () => {
    renderWithProviders(<WorkflowExampleCard {...defaultProps} isRemoving={true} />);

    const deleteButton = screen.getByRole("button", { name: /delete example/i });
    expect(deleteButton).toHaveAttribute("data-loading", "true");
  });
});
