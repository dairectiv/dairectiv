import { MantineProvider } from "@mantine/core";
import type { StepResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock dnd-kit
vi.mock("@dnd-kit/sortable", () => ({
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

import {
  SortableWorkflowStepCard,
  type SortableWorkflowStepCardProps,
} from "../components/sortable-workflow-step-card";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("SortableWorkflowStepCard", () => {
  const mockStep: StepResponse = {
    id: "step-1",
    content: "Test step content",
    order: 1,
    createdAt: new Date("2024-01-01"),
    updatedAt: new Date("2024-01-01"),
  };

  const defaultProps: SortableWorkflowStepCardProps = {
    step: mockStep,
    stepNumber: 1,
    onUpdate: vi.fn(),
    onRemove: vi.fn(),
    isUpdating: false,
    isRemoving: false,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render step number badge", () => {
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    expect(screen.getByText("Step 1")).toBeInTheDocument();
  });

  it("should render step content", () => {
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    expect(screen.getByText("Test step content")).toBeInTheDocument();
  });

  it("should render drag handle button", () => {
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /drag to reorder/i })).toBeInTheDocument();
  });

  it("should render edit button", () => {
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /edit step/i })).toBeInTheDocument();
  });

  it("should render delete button", () => {
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    expect(screen.getByRole("button", { name: /delete step/i })).toBeInTheDocument();
  });

  it("should show edit form when edit button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit step/i }));

    expect(screen.getByDisplayValue("Test step content")).toBeInTheDocument();
  });

  it("should hide edit form when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /edit step/i }));
    await user.click(screen.getByRole("button", { name: /cancel/i }));

    await waitFor(() => {
      expect(screen.queryByDisplayValue("Test step content")).not.toBeInTheDocument();
    });
  });

  it("should call onUpdate with correct values when save is clicked", async () => {
    const onUpdate = vi.fn();
    const user = userEvent.setup();
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} onUpdate={onUpdate} />);

    await user.click(screen.getByRole("button", { name: /edit step/i }));
    const textarea = screen.getByDisplayValue("Test step content");
    await user.clear(textarea);
    await user.type(textarea, "Updated content");
    await user.click(screen.getByRole("button", { name: /save/i }));

    await waitFor(() => {
      expect(onUpdate).toHaveBeenCalledWith("step-1", { content: "Updated content" });
    });
  });

  it("should open delete confirmation modal when delete button is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /delete step/i }));

    await waitFor(() => {
      expect(screen.getByText(/are you sure you want to delete this step/i)).toBeInTheDocument();
    });
  });

  it("should call onRemove when delete is confirmed", async () => {
    const onRemove = vi.fn();
    const user = userEvent.setup();
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} onRemove={onRemove} />);

    await user.click(screen.getByRole("button", { name: /delete step/i }));

    // Wait for modal to open
    const confirmButton = await screen.findByRole("button", { name: /^delete$/i });
    await user.click(confirmButton);

    await waitFor(() => {
      expect(onRemove).toHaveBeenCalledWith("step-1");
    });
  });

  it("should close delete modal when cancel is clicked", async () => {
    const user = userEvent.setup();
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} />);

    await user.click(screen.getByRole("button", { name: /delete step/i }));

    // Wait for modal to open
    const cancelButton = await screen.findByRole("button", { name: /cancel/i });
    await user.click(cancelButton);

    await waitFor(() => {
      expect(
        screen.queryByText(/are you sure you want to delete this step/i),
      ).not.toBeInTheDocument();
    });
  });

  it("should render with different step number", () => {
    renderWithProviders(<SortableWorkflowStepCard {...defaultProps} stepNumber={3} />);

    expect(screen.getByText("Step 3")).toBeInTheDocument();
  });
});
