import { MantineProvider } from "@mantine/core";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { CreateWorkflowForm, type CreateWorkflowFormProps } from "../components/create-workflow-form";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("CreateWorkflowForm", () => {
  const defaultProps: CreateWorkflowFormProps = {
    onSubmit: vi.fn(),
    isLoading: false,
    onCancel: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render form fields", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} />);

    expect(screen.getByLabelText(/name/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/description/i)).toBeInTheDocument();
    expect(screen.getByRole("button", { name: /create workflow/i })).toBeInTheDocument();
  });

  it("should render cancel button when onCancel is provided", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /cancel/i })).toBeInTheDocument();
  });

  it("should not render cancel button when onCancel is not provided", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} onCancel={undefined} />);

    expect(screen.queryByRole("button", { name: /cancel/i })).not.toBeInTheDocument();
  });

  it("should call onCancel when cancel button is clicked", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} />);

    fireEvent.click(screen.getByRole("button", { name: /cancel/i }));

    expect(defaultProps.onCancel).toHaveBeenCalled();
  });

  it("should call onSubmit with form values when form is valid", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<CreateWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    const nameInput = screen.getByLabelText(/name/i);
    const descriptionInput = screen.getByLabelText(/description/i);

    fireEvent.change(nameInput, { target: { value: "Test Workflow" } });
    fireEvent.change(descriptionInput, { target: { value: "Test Description" } });
    fireEvent.click(screen.getByRole("button", { name: /create workflow/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Test Workflow",
        description: "Test Description",
      });
    });
  });

  it("should not submit when name is empty", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<CreateWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    const descriptionInput = screen.getByLabelText(/description/i);
    fireEvent.change(descriptionInput, { target: { value: "Test Description" } });
    fireEvent.click(screen.getByRole("button", { name: /create workflow/i }));

    // Wait a bit for validation to run, then verify onSubmit was not called
    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when description is empty", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<CreateWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    const nameInput = screen.getByLabelText(/name/i);
    fireEvent.change(nameInput, { target: { value: "Test Workflow" } });
    fireEvent.click(screen.getByRole("button", { name: /create workflow/i }));

    // Wait a bit for validation to run, then verify onSubmit was not called
    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should disable form fields when loading", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} isLoading={true} />);

    expect(screen.getByLabelText(/name/i)).toBeDisabled();
    expect(screen.getByLabelText(/description/i)).toBeDisabled();
    expect(screen.getByRole("button", { name: /cancel/i })).toBeDisabled();
  });

  it("should show loading state on submit button", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} isLoading={true} />);

    // Mantine's Button with loading prop shows a loader
    const submitButton = screen.getByRole("button", { name: /create workflow/i });
    expect(submitButton).toBeInTheDocument();
    // The button should have the loading state - check for loader element
    const loader = submitButton.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should render helper text for fields", () => {
    renderWithProviders(<CreateWorkflowForm {...defaultProps} />);

    expect(screen.getByText(/a clear, descriptive name for this workflow/i)).toBeInTheDocument();
    expect(
      screen.getByText(/explain what this workflow is about and when it should be used/i),
    ).toBeInTheDocument();
  });
});
