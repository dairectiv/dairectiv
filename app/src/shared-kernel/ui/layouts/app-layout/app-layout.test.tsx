import { MantineProvider } from "@mantine/core";
import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";

// Mock TanStack Router
vi.mock("@tanstack/react-router", () => ({
  Link: ({ children, to }: { children: React.ReactNode; to: string }) => (
    <a href={to}>{children}</a>
  ),
  Outlet: () => <div data-testid="outlet" />,
}));

// Import after mocking
import { AppLayout } from "./app-layout";

function renderWithProviders(ui: React.ReactElement) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("AppLayout", () => {
  it("should render the app title", () => {
    renderWithProviders(<AppLayout>Content</AppLayout>);

    expect(screen.getByText("dairectiv")).toBeInTheDocument();
  });

  it("should render children content", () => {
    renderWithProviders(<AppLayout>Test Content</AppLayout>);

    expect(screen.getByText("Test Content")).toBeInTheDocument();
  });

  it("should render the home navigation link", () => {
    renderWithProviders(<AppLayout>Content</AppLayout>);

    expect(screen.getByText("Home")).toBeInTheDocument();
  });
});
