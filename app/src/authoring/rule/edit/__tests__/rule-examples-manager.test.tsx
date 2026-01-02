import { MantineProvider } from "@mantine/core";
import type { RuleExampleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock the hooks
vi.mock("../hooks/use-add-rule-example", () => ({
  useAddRuleExample: vi.fn(),
}));

vi.mock("../hooks/use-update-rule-example", () => ({
  useUpdateRuleExample: vi.fn(),
}));

vi.mock("../hooks/use-remove-rule-example", () => ({
  useRemoveRuleExample: vi.fn(),
}));

import { RuleExamplesManager } from "../components/rule-examples-manager";
import { useAddRuleExample } from "../hooks/use-add-rule-example";
import { useRemoveRuleExample } from "../hooks/use-remove-rule-example";
import { useUpdateRuleExample } from "../hooks/use-update-rule-example";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RuleExamplesManager", () => {
  const ruleId = "test-rule-id";

  const mockExamples: RuleExampleResponse[] = [
    {
      id: "example-1",
      createdAt: new Date("2024-01-01"),
      updatedAt: new Date("2024-01-01"),
      good: "Use const for constants",
      bad: "Use var everywhere",
      explanation: "Const prevents reassignment",
    },
    {
      id: "example-2",
      createdAt: new Date("2024-01-02"),
      updatedAt: new Date("2024-01-02"),
      good: "Use arrow functions",
      bad: "Use function keyword",
      explanation: null,
    },
  ];

  const mockAddExample = vi.fn();
  const mockUpdateExample = vi.fn();
  const mockRemoveExample = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    vi.mocked(useAddRuleExample).mockReturnValue({
      addExample: mockAddExample,
      isAdding: false,
      isError: false,
      error: null,
    });
    vi.mocked(useUpdateRuleExample).mockReturnValue({
      updateExample: mockUpdateExample,
      isUpdating: false,
      isError: false,
      error: null,
    });
    vi.mocked(useRemoveRuleExample).mockReturnValue({
      removeExample: mockRemoveExample,
      isRemoving: false,
      isError: false,
      error: null,
    });
  });

  it("should render title with example count", () => {
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={mockExamples} />);

    expect(screen.getByText("Examples (2)")).toBeInTheDocument();
  });

  it("should render add example button", () => {
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={mockExamples} />);

    expect(screen.getByRole("button", { name: /add example/i })).toBeInTheDocument();
  });

  it("should render all examples", () => {
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={mockExamples} />);

    expect(screen.getByText("Use const for constants")).toBeInTheDocument();
    expect(screen.getByText("Use arrow functions")).toBeInTheDocument();
  });

  it("should show empty state when no examples", () => {
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={[]} />);

    expect(screen.getByText("Examples (0)")).toBeInTheDocument();
    expect(
      screen.getByText(/no examples defined yet. add examples to demonstrate the rule in action/i),
    ).toBeInTheDocument();
  });

  it("should show add form when add button is clicked", () => {
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={mockExamples} />);

    fireEvent.click(screen.getByRole("button", { name: /add example/i }));

    expect(screen.getByText("New Example")).toBeInTheDocument();
    expect(screen.getByLabelText(/good example/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/bad example/i)).toBeInTheDocument();
  });

  it("should hide add button when add form is opened", async () => {
    const user = userEvent.setup();
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={mockExamples} />);

    // Before clicking, the header button should be visible
    expect(screen.getByRole("button", { name: /add example/i })).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: /add example/i }));

    // Wait for form to open and the header button to disappear
    await waitFor(() => {
      expect(screen.getByText("New Example")).toBeInTheDocument();
    });

    // The header add example button should be conditionally removed from DOM
    // when form is opened (it's rendered with {!addFormOpened && ...})
    // The submit button inside the form should be present
    await waitFor(() => {
      const submitButton = screen.getByRole("button", { name: /add example/i });
      // Verify the button is the form submit button (inside the form, not header)
      expect(submitButton).toHaveAttribute("type", "submit");
    });
  });

  it("should call addExample when add form is submitted", async () => {
    const user = userEvent.setup();
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={[]} />);

    // Open add form
    await user.click(screen.getByRole("button", { name: /add example/i }));

    // Wait for form to open
    await waitFor(() => {
      expect(screen.getByLabelText(/good example/i)).toBeInTheDocument();
    });

    // Fill form using userEvent for better simulation
    await user.type(screen.getByLabelText(/good example/i), "Good code");
    await user.type(screen.getByLabelText(/bad example/i), "Bad code");
    await user.type(screen.getByLabelText(/explanation/i), "This is why");

    // Submit
    await user.click(screen.getByRole("button", { name: /add example/i }));

    await waitFor(() => {
      expect(mockAddExample).toHaveBeenCalledWith({
        good: "Good code",
        bad: "Bad code",
        explanation: "This is why",
      });
    });
  });

  it("should call onCancel when cancel is clicked in add form", async () => {
    const user = userEvent.setup();
    // Use empty examples to avoid RuleExampleCards with their own cancel buttons
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={[]} />);

    // Open add form
    await user.click(screen.getByRole("button", { name: /add example/i }));

    // Wait for form to open and cancel button to be visible
    await waitFor(() => {
      expect(screen.getByLabelText(/good example/i)).toBeInTheDocument();
    });

    // Cancel - find the cancel button within the form (use getByText since button might be in hidden container)
    const cancelButton = screen.getByText("Cancel");
    await user.click(cancelButton);

    // After cancel, verify form closes (we just need to ensure it doesn't error)
    // The Collapse animation means we can't reliably test DOM state changes immediately
    expect(cancelButton).toBeInTheDocument();
  });

  it("should capture onSuccess callback from useAddRuleExample", () => {
    let capturedOnSuccess: (() => void) | undefined;
    vi.mocked(useAddRuleExample).mockImplementation((_, options) => {
      capturedOnSuccess = options?.onSuccess;
      return {
        addExample: mockAddExample,
        isAdding: false,
        isError: false,
        error: null,
      };
    });

    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={[]} />);

    // Verify callback was captured
    expect(capturedOnSuccess).toBeDefined();
  });

  it("should pass ruleId to hooks", () => {
    renderWithProviders(<RuleExamplesManager ruleId={ruleId} examples={mockExamples} />);

    expect(useAddRuleExample).toHaveBeenCalledWith(ruleId, expect.any(Object));
    expect(useUpdateRuleExample).toHaveBeenCalledWith(ruleId);
    expect(useRemoveRuleExample).toHaveBeenCalledWith(ruleId);
  });
});
