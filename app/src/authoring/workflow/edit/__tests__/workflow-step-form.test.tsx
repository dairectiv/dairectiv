import { MantineProvider } from "@mantine/core";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { WorkflowStepForm, type WorkflowStepFormProps } from "../components/workflow-step-form";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowStepForm", () => {
  const mockOnSubmit = vi.fn();
  const mockOnCancel = vi.fn();

  const defaultProps: WorkflowStepFormProps = {
    onSubmit: mockOnSubmit,
    onCancel: mockOnCancel,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render content textarea", () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    expect(screen.getByLabelText(/step content/i)).toBeInTheDocument();
  });

  it("should render submit button with default label", () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /save/i })).toBeInTheDocument();
  });

  it("should render submit button with custom label", () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} submitLabel="Add Step" />);

    expect(screen.getByRole("button", { name: /add step/i })).toBeInTheDocument();
  });

  it("should render cancel button", () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /cancel/i })).toBeInTheDocument();
  });

  it("should call onSubmit with form values when submitted", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    await user.type(screen.getByLabelText(/step content/i), "Test step content");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).toHaveBeenCalledWith({
        content: "Test step content",
      });
    });
  });

  it("should call onCancel when cancel button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /cancel/i }));

    expect(mockOnCancel).toHaveBeenCalled();
  });

  it("should pre-fill form with initial values", () => {
    renderWithProviders(
      <WorkflowStepForm {...defaultProps} initialValues={{ content: "Existing content" }} />,
    );

    expect(screen.getByLabelText(/step content/i)).toHaveValue("Existing content");
  });

  it("should not submit when content is empty", async () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    fireEvent.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when content is only whitespace", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepForm {...defaultProps} />);

    await user.type(screen.getByLabelText(/step content/i), "   ");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).not.toHaveBeenCalled();
    });
  });

  it("should disable inputs when loading", () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} isLoading={true} />);

    expect(screen.getByLabelText(/step content/i)).toBeDisabled();
    expect(screen.getByRole("button", { name: /cancel/i })).toBeDisabled();
  });

  it("should show loading state on submit button", () => {
    renderWithProviders(<WorkflowStepForm {...defaultProps} isLoading={true} />);

    const submitButton = screen.getByRole("button", { name: /save/i });
    expect(submitButton).toHaveAttribute("data-loading", "true");
  });
});
