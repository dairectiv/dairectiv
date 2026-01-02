import { MantineProvider } from "@mantine/core";
import { render, screen } from "@testing-library/react";
import type { ReactNode } from "react";
import { describe, expect, it } from "vitest";
import { RulesListEmpty } from "../components/rules-list-empty";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RulesListEmpty", () => {
  it("should render empty state message", () => {
    renderWithProviders(<RulesListEmpty />);

    expect(screen.getByText("No rules found")).toBeInTheDocument();
  });

  it("should render help text", () => {
    renderWithProviders(<RulesListEmpty />);

    expect(screen.getByText("Create your first rule to get started")).toBeInTheDocument();
  });

  it("should render inbox icon", () => {
    renderWithProviders(<RulesListEmpty />);

    const icon = document.querySelector("svg");
    expect(icon).toBeInTheDocument();
  });
});
