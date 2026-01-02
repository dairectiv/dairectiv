import { MantineProvider } from "@mantine/core";
import type { StepResponse } from "@shared/infrastructure/api/generated/types.gen";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock dnd-kit
vi.mock("@dnd-kit/core", () => ({
  DndContext: ({ children }: { children: ReactNode }) => <>{children}</>,
  closestCenter: vi.fn(),
  KeyboardSensor: vi.fn(),
  PointerSensor: vi.fn(),
  useSensor: vi.fn(),
  useSensors: vi.fn(() => []),
}));

vi.mock("@dnd-kit/sortable", () => ({
  SortableContext: ({ children }: { children: ReactNode }) => <>{children}</>,
  sortableKeyboardCoordinates: vi.fn(),
  verticalListSortingStrategy: vi.fn(),
  useSortable: () => ({
    attributes: { role: "button", tabIndex: 0 },
    listeners: {},
    setNodeRef: vi.fn(),
    transform: null,
    transition: undefined,
    isDragging: false,
  }),
}));

vi.mock("@dnd-kit/utilities", () => ({
  CSS: {
    Transform: {
      toString: () => undefined,
    },
  },
}));

// Mock the hooks
vi.mock("../hooks/use-add-workflow-step", () => ({
  useAddWorkflowStep: vi.fn(),
}));

vi.mock("../hooks/use-update-workflow-step", () => ({
  useUpdateWorkflowStep: vi.fn(),
}));

vi.mock("../hooks/use-remove-workflow-step", () => ({
  useRemoveWorkflowStep: vi.fn(),
}));

vi.mock("../hooks/use-move-workflow-step", () => ({
  useMoveWorkflowStep: vi.fn(),
}));

import { WorkflowStepsManager } from "../components/workflow-steps-manager";
import { useAddWorkflowStep } from "../hooks/use-add-workflow-step";
import { useMoveWorkflowStep } from "../hooks/use-move-workflow-step";
import { useRemoveWorkflowStep } from "../hooks/use-remove-workflow-step";
import { useUpdateWorkflowStep } from "../hooks/use-update-workflow-step";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("WorkflowStepsManager", () => {
  const workflowId = "test-workflow-id";

  const mockSteps: StepResponse[] = [
    {
      id: "step-1",
      content: "Review the code changes",
      order: 1,
      createdAt: new Date("2024-01-01"),
      updatedAt: new Date("2024-01-01"),
    },
    {
      id: "step-2",
      content: "Check for tests",
      order: 2,
      createdAt: new Date("2024-01-02"),
      updatedAt: new Date("2024-01-02"),
    },
  ];

  const mockAddStep = vi.fn();
  const mockUpdateStep = vi.fn();
  const mockRemoveStep = vi.fn();
  const mockMoveStep = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
    vi.mocked(useAddWorkflowStep).mockReturnValue({
      addStep: mockAddStep,
      isAdding: false,
      isError: false,
      error: null,
    });
    vi.mocked(useUpdateWorkflowStep).mockReturnValue({
      updateStep: mockUpdateStep,
      isUpdating: false,
      isError: false,
      error: null,
    });
    vi.mocked(useRemoveWorkflowStep).mockReturnValue({
      removeStep: mockRemoveStep,
      isRemoving: false,
      isError: false,
      error: null,
    });
    vi.mocked(useMoveWorkflowStep).mockReturnValue({
      moveStep: mockMoveStep,
      isMoving: false,
      isError: false,
      error: null,
    });
  });

  it("should render title with step count", () => {
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    expect(screen.getByText("Steps (2)")).toBeInTheDocument();
  });

  it("should render add step button", () => {
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    expect(screen.getByRole("button", { name: /add step/i })).toBeInTheDocument();
  });

  it("should render all steps", () => {
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    expect(screen.getByText("Review the code changes")).toBeInTheDocument();
    expect(screen.getByText("Check for tests")).toBeInTheDocument();
  });

  it("should show empty state when no steps", () => {
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={[]} />);

    expect(screen.getByText("Steps (0)")).toBeInTheDocument();
    expect(
      screen.getByText(/no steps defined yet. add steps to guide the workflow execution/i),
    ).toBeInTheDocument();
  });

  it("should show add form when add button is clicked", () => {
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    fireEvent.click(screen.getByRole("button", { name: /add step/i }));

    expect(screen.getByText("New Step")).toBeInTheDocument();
    expect(screen.getByLabelText(/step content/i)).toBeInTheDocument();
  });

  it("should hide add button when add form is opened", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    // Before clicking, the header button should be visible
    expect(screen.getByRole("button", { name: /add step/i })).toBeInTheDocument();

    await user.click(screen.getByRole("button", { name: /add step/i }));

    // Wait for form to open
    await waitFor(() => {
      expect(screen.getByText("New Step")).toBeInTheDocument();
    });

    // The form submit button should be present
    await waitFor(() => {
      const submitButton = screen.getByRole("button", { name: /add step/i });
      expect(submitButton).toHaveAttribute("type", "submit");
    });
  });

  it("should call addStep when add form is submitted", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={[]} />);

    // Open add form
    await user.click(screen.getByRole("button", { name: /add step/i }));

    // Wait for form to open
    await waitFor(() => {
      expect(screen.getByLabelText(/step content/i)).toBeInTheDocument();
    });

    // Fill form
    await user.type(screen.getByLabelText(/step content/i), "New step content");

    // Submit
    await user.click(screen.getByRole("button", { name: /add step/i }));

    await waitFor(() => {
      expect(mockAddStep).toHaveBeenCalledWith({
        content: "New step content",
        afterStepId: null,
      });
    });
  });

  it("should add step after last step when steps exist", async () => {
    const user = userEvent.setup();
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    // Open add form
    await user.click(screen.getByRole("button", { name: /add step/i }));

    // Wait for form to open
    await waitFor(() => {
      expect(screen.getByLabelText(/step content/i)).toBeInTheDocument();
    });

    // Fill form
    await user.type(screen.getByLabelText(/step content/i), "New step at end");

    // Submit
    await user.click(screen.getByRole("button", { name: /add step/i }));

    await waitFor(() => {
      expect(mockAddStep).toHaveBeenCalledWith({
        content: "New step at end",
        afterStepId: "step-2", // After the last step
      });
    });
  });

  it("should pass workflowId to hooks", () => {
    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={mockSteps} />);

    expect(useAddWorkflowStep).toHaveBeenCalledWith(workflowId, expect.any(Object));
    expect(useUpdateWorkflowStep).toHaveBeenCalledWith(workflowId);
    expect(useRemoveWorkflowStep).toHaveBeenCalledWith(workflowId);
  });

  it("should render steps in order", () => {
    const unorderedSteps: StepResponse[] = [
      {
        id: "step-3",
        content: "Third step",
        order: 3,
        createdAt: new Date("2024-01-01"),
        updatedAt: new Date("2024-01-01"),
      },
      {
        id: "step-1",
        content: "First step",
        order: 1,
        createdAt: new Date("2024-01-01"),
        updatedAt: new Date("2024-01-01"),
      },
      {
        id: "step-2",
        content: "Second step",
        order: 2,
        createdAt: new Date("2024-01-01"),
        updatedAt: new Date("2024-01-01"),
      },
    ];

    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={unorderedSteps} />);

    const stepBadges = screen.getAllByText(/^Step \d$/);
    expect(stepBadges[0]).toHaveTextContent("Step 1");
    expect(stepBadges[1]).toHaveTextContent("Step 2");
    expect(stepBadges[2]).toHaveTextContent("Step 3");
  });

  it("should capture onSuccess callback from useAddWorkflowStep", () => {
    let capturedOnSuccess: (() => void) | undefined;
    vi.mocked(useAddWorkflowStep).mockImplementation((_, options) => {
      capturedOnSuccess = options?.onSuccess;
      return {
        addStep: mockAddStep,
        isAdding: false,
        isError: false,
        error: null,
      };
    });

    renderWithProviders(<WorkflowStepsManager workflowId={workflowId} steps={[]} />);

    // Verify callback was captured
    expect(capturedOnSuccess).toBeDefined();
  });
});
