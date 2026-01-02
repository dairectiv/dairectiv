import { MantineProvider } from "@mantine/core";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { EditRuleForm, type EditRuleFormProps } from "../components/edit-rule-form";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("EditRuleForm", () => {
  const defaultInitialValues = {
    name: "Existing Rule",
    description: "Existing Description",
    content: "Existing Content",
  };

  const defaultProps: EditRuleFormProps = {
    initialValues: defaultInitialValues,
    onSubmit: vi.fn(),
    isLoading: false,
    onCancel: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render form fields with initial values", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} />);

    expect(screen.getByLabelText(/name/i)).toHaveValue("Existing Rule");
    expect(screen.getByLabelText(/description/i)).toHaveValue("Existing Description");
    expect(screen.getByLabelText(/content/i)).toHaveValue("Existing Content");
    expect(screen.getByRole("button", { name: /save changes/i })).toBeInTheDocument();
  });

  it("should render cancel button when onCancel is provided", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /cancel/i })).toBeInTheDocument();
  });

  it("should not render cancel button when onCancel is not provided", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} onCancel={undefined} />);

    expect(screen.queryByRole("button", { name: /cancel/i })).not.toBeInTheDocument();
  });

  it("should call onCancel when cancel button is clicked", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} />);

    fireEvent.click(screen.getByRole("button", { name: /cancel/i }));

    expect(defaultProps.onCancel).toHaveBeenCalled();
  });

  it("should call onSubmit with form values when form is valid", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditRuleForm {...defaultProps} onSubmit={onSubmit} />);

    const nameInput = screen.getByLabelText(/name/i);
    const descriptionInput = screen.getByLabelText(/description/i);
    const contentInput = screen.getByLabelText(/content/i);

    fireEvent.change(nameInput, { target: { value: "Updated Rule" } });
    fireEvent.change(descriptionInput, { target: { value: "Updated Description" } });
    fireEvent.change(contentInput, { target: { value: "Updated Content" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Updated Rule",
        description: "Updated Description",
        content: "Updated Content",
      });
    });
  });

  it("should submit with unchanged values", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditRuleForm {...defaultProps} onSubmit={onSubmit} />);

    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Existing Rule",
        description: "Existing Description",
        content: "Existing Content",
      });
    });
  });

  it("should submit with undefined content when content is empty", async () => {
    const onSubmit = vi.fn();
    const propsWithEmptyContent: EditRuleFormProps = {
      ...defaultProps,
      initialValues: {
        name: "Test Rule",
        description: "Test Description",
        content: "",
      },
      onSubmit,
    };
    renderWithProviders(<EditRuleForm {...propsWithEmptyContent} />);

    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Test Rule",
        description: "Test Description",
        content: undefined,
      });
    });
  });

  it("should not submit when name is cleared", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditRuleForm {...defaultProps} onSubmit={onSubmit} />);

    const nameInput = screen.getByLabelText(/name/i);
    fireEvent.change(nameInput, { target: { value: "" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when description is cleared", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditRuleForm {...defaultProps} onSubmit={onSubmit} />);

    const descriptionInput = screen.getByLabelText(/description/i);
    fireEvent.change(descriptionInput, { target: { value: "" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should allow empty content field", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<EditRuleForm {...defaultProps} onSubmit={onSubmit} />);

    const contentInput = screen.getByLabelText(/content/i);
    fireEvent.change(contentInput, { target: { value: "" } });
    fireEvent.click(screen.getByRole("button", { name: /save changes/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        name: "Existing Rule",
        description: "Existing Description",
        content: undefined,
      });
    });
  });

  it("should disable form fields when loading", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} isLoading={true} />);

    expect(screen.getByLabelText(/name/i)).toBeDisabled();
    expect(screen.getByLabelText(/description/i)).toBeDisabled();
    expect(screen.getByLabelText(/content/i)).toBeDisabled();
    expect(screen.getByRole("button", { name: /cancel/i })).toBeDisabled();
  });

  it("should show loading state on submit button", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} isLoading={true} />);

    const submitButton = screen.getByRole("button", { name: /save changes/i });
    expect(submitButton).toBeInTheDocument();
    // The button should have the loading state - check for loader element
    const loader = submitButton.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should render helper text for fields", () => {
    renderWithProviders(<EditRuleForm {...defaultProps} />);

    expect(screen.getByText(/a clear, descriptive name for this rule/i)).toBeInTheDocument();
    expect(
      screen.getByText(/explain what this rule is about and when it should be used/i),
    ).toBeInTheDocument();
    expect(screen.getByText(/the actual content or body of the rule/i)).toBeInTheDocument();
  });
});
