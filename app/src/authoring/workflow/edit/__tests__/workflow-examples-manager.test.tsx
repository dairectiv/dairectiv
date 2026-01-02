import { MantineProvider } from "@mantine/core";
import type { WorkflowExampleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock the hooks
const mockAddExample = vi.fn();
const mockUpdateExample = vi.fn();
const mockRemoveExample = vi.fn();

vi.mock("../hooks/use-add-workflow-example", () => ({
  useAddWorkflowExample: vi.fn(() => ({
    addExample: mockAddExample,
    isAdding: false,
  })),
}));

vi.mock("../hooks/use-update-workflow-example", () => ({
  useUpdateWorkflowExample: vi.fn(() => ({
    updateExample: mockUpdateExample,
    isUpdating: false,
  })),
}));

vi.mock("../hooks/use-remove-workflow-example", () => ({
  useRemoveWorkflowExample: vi.fn(() => ({
    removeExample: mockRemoveExample,
    isRemoving: false,
  })),
}));

import {
  WorkflowExamplesManager,
  type WorkflowExamplesManagerProps,
} from "../components/workflow-examples-manager";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowExamplesManager", () => {
  const workflowId = "test-workflow-id";

  const mockExamples: WorkflowExampleResponse[] = [
    {
      id: "example-1",
      scenario: "First scenario",
      input: "First input",
      output: "First output",
      explanation: "First explanation",
      createdAt: new Date("2024-01-01"),
      updatedAt: new Date("2024-01-01"),
    },
    {
      id: "example-2",
      scenario: "Second scenario",
      input: "Second input",
      output: "Second output",
      explanation: null,
      createdAt: new Date("2024-01-02"),
      updatedAt: new Date("2024-01-02"),
    },
  ];

  const defaultProps: WorkflowExamplesManagerProps = {
    workflowId,
    examples: mockExamples,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render title", () => {
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    expect(screen.getByText("Examples")).toBeInTheDocument();
  });

  it("should render all examples", () => {
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    expect(screen.getByText("First scenario")).toBeInTheDocument();
    expect(screen.getByText("Second scenario")).toBeInTheDocument();
  });

  it("should render add button", () => {
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    expect(screen.getByRole("button", { name: /add example/i })).toBeInTheDocument();
  });

  it("should show empty state when no examples", () => {
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} examples={[]} />);

    expect(screen.getByText(/no examples yet/i)).toBeInTheDocument();
  });

  it("should show add form when add button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /add example/i }));

    await waitFor(() => {
      expect(screen.getByText("New Example")).toBeInTheDocument();
    });
  });

  it("should hide add button when add form is opened", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    // Click add button
    expect(screen.getByRole("button", { name: /add example/i })).toBeInTheDocument();
    await user.click(screen.getByRole("button", { name: /add example/i }));

    // Wait for form to appear
    await waitFor(() => {
      expect(screen.getByText("New Example")).toBeInTheDocument();
    });

    // Now the add button should be the submit button in the form
    await waitFor(() => {
      const submitButton = screen.getByRole("button", { name: /add example/i });
      expect(submitButton).toHaveAttribute("type", "submit");
    });
  });

  it("should close add form when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /add example/i }));

    // Wait for the cancel button to be visible (Collapse animation)
    const cancelButton = await screen.findByRole("button", { name: /cancel/i });

    await user.click(cancelButton);

    // Wait for the add button to reappear (not be a submit button)
    await waitFor(() => {
      const addButton = screen.getByRole("button", { name: /add example/i });
      expect(addButton).not.toHaveAttribute("type", "submit");
    });
  });

  it("should call addExample when form is submitted", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /add example/i }));

    await waitFor(() => {
      expect(screen.getByText("New Example")).toBeInTheDocument();
    });

    await user.type(screen.getByLabelText(/scenario/i), "Test scenario");
    await user.type(screen.getByLabelText(/^input/i), "Test input");
    await user.type(screen.getByLabelText(/output/i), "Test output");
    await user.click(screen.getByRole("button", { name: /add example/i }));

    await waitFor(() => {
      expect(mockAddExample).toHaveBeenCalledWith({
        scenario: "Test scenario",
        input: "Test input",
        output: "Test output",
        explanation: null,
      });
    });
  });

  it("should render example numbers correctly", () => {
    renderWithProviders(<WorkflowExamplesManager {...defaultProps} />);

    expect(screen.getByText("Example 1")).toBeInTheDocument();
    expect(screen.getByText("Example 2")).toBeInTheDocument();
  });
});
