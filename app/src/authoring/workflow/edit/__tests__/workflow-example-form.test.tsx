import { MantineProvider } from "@mantine/core";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import {
  WorkflowExampleForm,
  type WorkflowExampleFormProps,
} from "../components/workflow-example-form";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowExampleForm", () => {
  const mockOnSubmit = vi.fn();
  const mockOnCancel = vi.fn();

  const defaultProps: WorkflowExampleFormProps = {
    onSubmit: mockOnSubmit,
    onCancel: mockOnCancel,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render scenario textarea", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    expect(screen.getByLabelText(/scenario/i)).toBeInTheDocument();
  });

  it("should render input textarea", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    expect(screen.getByLabelText(/^input/i)).toBeInTheDocument();
  });

  it("should render output textarea", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    expect(screen.getByLabelText(/output/i)).toBeInTheDocument();
  });

  it("should render explanation textarea", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    expect(screen.getByLabelText(/explanation/i)).toBeInTheDocument();
  });

  it("should render submit button with default label", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /save/i })).toBeInTheDocument();
  });

  it("should render submit button with custom label", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} submitLabel="Add Example" />);

    expect(screen.getByRole("button", { name: /add example/i })).toBeInTheDocument();
  });

  it("should render cancel button", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /cancel/i })).toBeInTheDocument();
  });

  it("should call onSubmit with form values when submitted", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    await user.type(screen.getByLabelText(/scenario/i), "Test scenario");
    await user.type(screen.getByLabelText(/^input/i), "Test input");
    await user.type(screen.getByLabelText(/output/i), "Test output");
    await user.type(screen.getByLabelText(/explanation/i), "Test explanation");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).toHaveBeenCalledWith({
        scenario: "Test scenario",
        input: "Test input",
        output: "Test output",
        explanation: "Test explanation",
      });
    });
  });

  it("should call onCancel when cancel button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /cancel/i }));

    expect(mockOnCancel).toHaveBeenCalled();
  });

  it("should pre-fill form with initial values", () => {
    renderWithProviders(
      <WorkflowExampleForm
        {...defaultProps}
        initialValues={{
          scenario: "Initial scenario",
          input: "Initial input",
          output: "Initial output",
          explanation: "Initial explanation",
        }}
      />,
    );

    expect(screen.getByLabelText(/scenario/i)).toHaveValue("Initial scenario");
    expect(screen.getByLabelText(/^input/i)).toHaveValue("Initial input");
    expect(screen.getByLabelText(/output/i)).toHaveValue("Initial output");
    expect(screen.getByLabelText(/explanation/i)).toHaveValue("Initial explanation");
  });

  it("should not submit when scenario is empty", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    await user.type(screen.getByLabelText(/^input/i), "Test input");
    await user.type(screen.getByLabelText(/output/i), "Test output");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when input is empty", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    await user.type(screen.getByLabelText(/scenario/i), "Test scenario");
    await user.type(screen.getByLabelText(/output/i), "Test output");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when output is empty", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExampleForm {...defaultProps} />);

    await user.type(screen.getByLabelText(/scenario/i), "Test scenario");
    await user.type(screen.getByLabelText(/^input/i), "Test input");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(mockOnSubmit).not.toHaveBeenCalled();
    });
  });

  it("should disable inputs when loading", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} isLoading={true} />);

    expect(screen.getByLabelText(/scenario/i)).toBeDisabled();
    expect(screen.getByLabelText(/^input/i)).toBeDisabled();
    expect(screen.getByLabelText(/output/i)).toBeDisabled();
    expect(screen.getByLabelText(/explanation/i)).toBeDisabled();
    expect(screen.getByRole("button", { name: /cancel/i })).toBeDisabled();
  });

  it("should show loading state on submit button", () => {
    renderWithProviders(<WorkflowExampleForm {...defaultProps} isLoading={true} />);

    const submitButton = screen.getByRole("button", { name: /save/i });
    expect(submitButton).toHaveAttribute("data-loading", "true");
  });
});
