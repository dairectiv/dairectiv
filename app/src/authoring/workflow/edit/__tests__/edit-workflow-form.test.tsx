import { MantineProvider } from "@mantine/core";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { EditWorkflowForm, type EditWorkflowFormProps } from "../components/edit-workflow-form";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("EditWorkflowForm", () => {
  const defaultInitialValues = {
    name: "Existing Workflow",
    description: "Existing Description",
  };

  const defaultProps: EditWorkflowFormProps = {
    initialValues: defaultInitialValues,
    onSubmit: vi.fn(),
    isLoading: false,
    onCancel: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render form fields with initial values", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} />);

    expect(screen.getByLabelText(/name/i)).toHaveValue("Existing Workflow");
    expect(screen.getByLabelText(/description/i)).toHaveValue("Existing Description");
    expect(screen.getByRole("button", { name: /save changes/i })).toBeInTheDocument();
  });

  it("should render cancel button when onCancel is provided", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /cancel/i })).toBeInTheDocument();
  });

  it("should not render cancel button when onCancel is not provided", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} onCancel={undefined} />);

    expect(screen.queryByRole("button", { name: /cancel/i })).not.toBeInTheDocument();
  });

  it("should call onCancel when cancel button is clicked", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} />);

    fireEvent.click(screen.getByRole("button", { name: /cancel/i }));

    expect(defaultProps.onCancel).toHaveBeenCalled();
  });

  it("should call onSubmit with form values when form is valid", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    const nameInput = screen.getByLabelText(/name/i);
    const descriptionInput = screen.getByLabelText(/description/i);

    fireEvent.change(nameInput, { target: { value: "Updated Workflow" } });
    fireEvent.change(descriptionInput, { target: { value: "Updated Description" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Updated Workflow",
        description: "Updated Description",
      });
    });
  });

  it("should submit with unchanged values", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Existing Workflow",
        description: "Existing Description",
      });
    });
  });

  it("should not submit when name is cleared", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    const nameInput = screen.getByLabelText(/name/i);
    fireEvent.change(nameInput, { target: { value: "" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when description is cleared", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditWorkflowForm {...defaultProps} onSubmit={onSubmit} />);

    const descriptionInput = screen.getByLabelText(/description/i);
    fireEvent.change(descriptionInput, { target: { value: "" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should disable form fields when loading", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} isLoading={true} />);

    expect(screen.getByLabelText(/name/i)).toBeDisabled();
    expect(screen.getByLabelText(/description/i)).toBeDisabled();
    expect(screen.getByRole("button", { name: /cancel/i })).toBeDisabled();
  });

  it("should show loading state on submit button", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} isLoading={true} />);

    const submitButton = screen.getByRole("button", { name: /save changes/i });
    expect(submitButton).toBeInTheDocument();
    // The button should have the loading state - check for loader element
    const loader = submitButton.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should render helper text for fields", () => {
    renderWithProviders(<EditWorkflowForm {...defaultProps} />);

    expect(screen.getByText(/a clear, descriptive name for this workflow/i)).toBeInTheDocument();
    expect(
      screen.getByText(/explain what this workflow is about and when it should be used/i),
    ).toBeInTheDocument();
  });
});
