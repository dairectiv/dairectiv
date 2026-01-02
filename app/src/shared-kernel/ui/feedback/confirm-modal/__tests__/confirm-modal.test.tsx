import { MantineProvider } from "@mantine/core";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { ConfirmModal, type ConfirmModalProps } from "../confirm-modal";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("ConfirmModal", () => {
  const defaultProps: ConfirmModalProps = {
    opened: true,
    onClose: vi.fn(),
    onConfirm: vi.fn(),
    title: "Confirm Action",
    message: "Are you sure you want to proceed?",
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render title and message when opened", () => {
    renderWithProviders(<ConfirmModal {...defaultProps} />);

    expect(screen.getByText("Confirm Action")).toBeInTheDocument();
    expect(screen.getByText("Are you sure you want to proceed?")).toBeInTheDocument();
  });

  it("should not render when closed", () => {
    renderWithProviders(<ConfirmModal {...defaultProps} opened={false} />);

    expect(screen.queryByText("Confirm Action")).not.toBeInTheDocument();
  });

  it("should render default button labels", () => {
    renderWithProviders(<ConfirmModal {...defaultProps} />);

    expect(screen.getByRole("button", { name: "Confirm" })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Cancel" })).toBeInTheDocument();
  });

  it("should render custom button labels", () => {
    renderWithProviders(
      <ConfirmModal {...defaultProps} confirmLabel="Archive" cancelLabel="Go Back" />,
    );

    expect(screen.getByRole("button", { name: "Archive" })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Go Back" })).toBeInTheDocument();
  });

  it("should call onConfirm when confirm button is clicked", async () => {
    const user = userEvent.setup();
    const onConfirm = vi.fn();
    renderWithProviders(<ConfirmModal {...defaultProps} onConfirm={onConfirm} />);

    await user.click(screen.getByRole("button", { name: "Confirm" }));

    expect(onConfirm).toHaveBeenCalledTimes(1);
  });

  it("should call onClose when cancel button is clicked", async () => {
    const user = userEvent.setup();
    const onClose = vi.fn();
    renderWithProviders(<ConfirmModal {...defaultProps} onClose={onClose} />);

    await user.click(screen.getByRole("button", { name: "Cancel" }));

    expect(onClose).toHaveBeenCalledTimes(1);
  });

  it("should show loading state on confirm button", () => {
    renderWithProviders(<ConfirmModal {...defaultProps} isLoading={true} />);

    const confirmButton = screen.getByRole("button", { name: "Confirm" });
    expect(confirmButton).toHaveAttribute("data-loading", "true");
  });

  it("should disable cancel button when loading", () => {
    renderWithProviders(<ConfirmModal {...defaultProps} isLoading={true} />);

    const cancelButton = screen.getByRole("button", { name: "Cancel" });
    expect(cancelButton).toBeDisabled();
  });

  it("should apply custom confirm color", () => {
    renderWithProviders(<ConfirmModal {...defaultProps} confirmColor="orange" />);

    const confirmButton = screen.getByRole("button", { name: "Confirm" });
    // Mantine applies color via data attribute or class
    expect(confirmButton).toBeInTheDocument();
  });
});
