import { MantineProvider } from "@mantine/core";
import { render, screen } from "@testing-library/react";
import type { ReactNode } from "react";
import { describe, expect, it, vi } from "vitest";
import { AppLayout } from "./app-layout";

// Mock TanStack Router
vi.mock("@tanstack/react-router", () => ({
  Outlet: () => <div data-testid="outlet">Outlet</div>,
  Link: ({ children, to }: { children: ReactNode; to: string }) => <a href={to}>{children}</a>,
  useRouterState: () => ({ location: { pathname: "/" } }),
}));

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("AppLayout", () => {
  it("should render children", () => {
    renderWithProviders(
      <AppLayout>
        <div data-testid="content">Test Content</div>
      </AppLayout>,
    );

    expect(screen.getByTestId("content")).toBeInTheDocument();
  });

  it("should render outlet when no children provided", () => {
    renderWithProviders(<AppLayout />);

    expect(screen.getByTestId("outlet")).toBeInTheDocument();
  });
});
