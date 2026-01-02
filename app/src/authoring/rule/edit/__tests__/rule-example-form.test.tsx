import { MantineProvider } from "@mantine/core";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

import { RuleExampleForm, type RuleExampleFormProps } from "../components/rule-example-form";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RuleExampleForm", () => {
  const defaultProps: RuleExampleFormProps = {
    onSubmit: vi.fn(),
    onCancel: vi.fn(),
    isLoading: false,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render form fields with empty initial values", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} />);

    expect(screen.getByLabelText(/good example/i)).toHaveValue("");
    expect(screen.getByLabelText(/bad example/i)).toHaveValue("");
    expect(screen.getByLabelText(/explanation/i)).toHaveValue("");
  });

  it("should render form fields with provided initial values", () => {
    renderWithProviders(
      <RuleExampleForm
        {...defaultProps}
        initialValues={{
          good: "Good code",
          bad: "Bad code",
          explanation: "This is why",
        }}
      />,
    );

    expect(screen.getByLabelText(/good example/i)).toHaveValue("Good code");
    expect(screen.getByLabelText(/bad example/i)).toHaveValue("Bad code");
    expect(screen.getByLabelText(/explanation/i)).toHaveValue("This is why");
  });

  it("should render cancel and submit buttons", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} />);

    expect(screen.getByRole("button", { name: /cancel/i })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: /save/i })).toBeInTheDocument();
  });

  it("should render custom submit label", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} submitLabel="Add Example" />);

    expect(screen.getByRole("button", { name: /add example/i })).toBeInTheDocument();
  });

  it("should call onCancel when cancel button is clicked", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} />);

    fireEvent.click(screen.getByRole("button", { name: /cancel/i }));

    expect(defaultProps.onCancel).toHaveBeenCalled();
  });

  it("should call onSubmit with form values when form is valid", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<RuleExampleForm {...defaultProps} onSubmit={onSubmit} />);

    fireEvent.change(screen.getByLabelText(/good example/i), { target: { value: "Use const" } });
    fireEvent.change(screen.getByLabelText(/bad example/i), { target: { value: "Use var" } });
    fireEvent.change(screen.getByLabelText(/explanation/i), {
      target: { value: "Const is safer" },
    });
    fireEvent.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        good: "Use const",
        bad: "Use var",
        explanation: "Const is safer",
      });
    });
  });

  it("should not submit when good example is empty", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<RuleExampleForm {...defaultProps} onSubmit={onSubmit} />);

    fireEvent.change(screen.getByLabelText(/bad example/i), { target: { value: "Use var" } });
    fireEvent.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should not submit when bad example is empty", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<RuleExampleForm {...defaultProps} onSubmit={onSubmit} />);

    fireEvent.change(screen.getByLabelText(/good example/i), { target: { value: "Use const" } });
    fireEvent.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(onSubmit).not.toHaveBeenCalled();
    });
  });

  it("should allow empty explanation", async () => {
    const onSubmit = vi.fn();
    renderWithProviders(<RuleExampleForm {...defaultProps} onSubmit={onSubmit} />);

    fireEvent.change(screen.getByLabelText(/good example/i), { target: { value: "Use const" } });
    fireEvent.change(screen.getByLabelText(/bad example/i), { target: { value: "Use var" } });
    fireEvent.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(onSubmit).toHaveBeenCalledWith({
        good: "Use const",
        bad: "Use var",
        explanation: "",
      });
    });
  });

  it("should disable form fields when loading", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} isLoading={true} />);

    expect(screen.getByLabelText(/good example/i)).toBeDisabled();
    expect(screen.getByLabelText(/bad example/i)).toBeDisabled();
    expect(screen.getByLabelText(/explanation/i)).toBeDisabled();
    expect(screen.getByRole("button", { name: /cancel/i })).toBeDisabled();
  });

  it("should show loading state on submit button", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} isLoading={true} />);

    const submitButton = screen.getByRole("button", { name: /save/i });
    const loader = submitButton.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should render helper text for fields", () => {
    renderWithProviders(<RuleExampleForm {...defaultProps} />);

    expect(screen.getByText(/show how the rule should be followed correctly/i)).toBeInTheDocument();
    expect(screen.getByText(/show how the rule is commonly violated/i)).toBeInTheDocument();
    expect(
      screen.getByText(/help users understand why the good example is preferred/i),
    ).toBeInTheDocument();
  });
});
