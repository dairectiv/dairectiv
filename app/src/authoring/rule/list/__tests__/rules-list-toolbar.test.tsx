import { MantineProvider } from "@mantine/core";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { RulesListToolbar, type RulesListToolbarProps } from "../components/rules-list-toolbar";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RulesListToolbar", () => {
  const defaultProps: RulesListToolbarProps = {
    onSearchChange: vi.fn(),
    onStateChange: vi.fn(),
    onSortChange: vi.fn(),
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render search input", () => {
    renderWithProviders(<RulesListToolbar {...defaultProps} />);

    expect(screen.getByPlaceholderText("Search rules...")).toBeInTheDocument();
  });

  it("should render state filter dropdown", () => {
    renderWithProviders(<RulesListToolbar {...defaultProps} />);

    // Mantine Select renders as an input with placeholder
    expect(screen.getByPlaceholderText("Filter by state")).toBeInTheDocument();
  });

  it("should render sort dropdown", () => {
    renderWithProviders(<RulesListToolbar {...defaultProps} />);

    expect(screen.getByPlaceholderText("Sort by")).toBeInTheDocument();
  });

  it("should display initial search value", () => {
    renderWithProviders(<RulesListToolbar {...defaultProps} search="test query" />);

    const searchInput = screen.getByPlaceholderText("Search rules...");
    expect(searchInput).toHaveValue("test query");
  });

  it("should call onSearchChange after debounce", async () => {
    vi.useFakeTimers();
    const onSearchChange = vi.fn();
    renderWithProviders(<RulesListToolbar {...defaultProps} onSearchChange={onSearchChange} />);

    const searchInput = screen.getByPlaceholderText("Search rules...");
    fireEvent.change(searchInput, { target: { value: "new search" } });

    // Debounce is 300ms
    expect(onSearchChange).not.toHaveBeenCalled();

    await vi.advanceTimersByTimeAsync(300);

    expect(onSearchChange).toHaveBeenCalledWith("new search");

    vi.useRealTimers();
  });

  it("should call onStateChange when state filter changes", async () => {
    const onStateChange = vi.fn();
    renderWithProviders(<RulesListToolbar {...defaultProps} onStateChange={onStateChange} />);

    const stateSelect = screen.getByPlaceholderText("Filter by state");
    fireEvent.click(stateSelect);

    const draftOption = await screen.findByRole("option", { name: "Draft" });
    fireEvent.click(draftOption);

    await waitFor(() => {
      expect(onStateChange).toHaveBeenCalledWith("draft");
    });
  });

  it("should call onSortChange when sort changes", async () => {
    const onSortChange = vi.fn();
    renderWithProviders(<RulesListToolbar {...defaultProps} onSortChange={onSortChange} />);

    const sortSelect = screen.getByPlaceholderText("Sort by");
    fireEvent.click(sortSelect);

    const nameOption = await screen.findByRole("option", { name: "Name A-Z" });
    fireEvent.click(nameOption);

    await waitFor(() => {
      expect(onSortChange).toHaveBeenCalledWith("name", "asc");
    });
  });

  it("should clear state filter when clearing selection", async () => {
    const onStateChange = vi.fn();
    renderWithProviders(
      <RulesListToolbar {...defaultProps} state="draft" onStateChange={onStateChange} />,
    );

    const stateSelect = screen.getByPlaceholderText("Filter by state");
    fireEvent.click(stateSelect);

    const allOption = await screen.findByRole("option", { name: "All states" });
    fireEvent.click(allOption);

    await waitFor(() => {
      expect(onStateChange).toHaveBeenCalledWith(undefined);
    });
  });
});
