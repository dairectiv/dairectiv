import { MantineProvider } from "@mantine/core";
import type { RuleResponse } from "@shared/infrastructure/api/generated/types.gen";
import { render, screen } from "@testing-library/react";
import type { ReactNode } from "react";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Import directly from component file to avoid triggering router initialization from barrel export
import { RuleDetail, type RuleDetailProps } from "../components/rule-detail";

function renderWithProviders(ui: ReactNode) {
  return render(<MantineProvider>{ui}</MantineProvider>);
}

describe("RuleDetail", () => {
  const mockRule: RuleResponse = {
    id: "rule-1",
    name: "Code Style Rule",
    description: "Guidelines for code formatting and style",
    state: "draft",
    content: "This is the rule content explaining the guidelines.",
    createdAt: new Date("2025-01-01T00:00:00Z"),
    updatedAt: new Date("2025-01-01T00:00:00Z"),
    examples: [
      {
        id: "example-1",
        good: "const userName = 'John';",
        bad: "const user_name = 'John';",
        explanation: "Use camelCase for variable names",
        createdAt: new Date("2025-01-01T00:00:00Z"),
        updatedAt: new Date("2025-01-01T00:00:00Z"),
      },
    ],
  };

  const defaultProps: RuleDetailProps = {
    rule: mockRule,
    isLoading: false,
    isError: false,
    error: null,
  };

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("should render rule name and description", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByText("Code Style Rule")).toBeInTheDocument();
    expect(screen.getByText("Guidelines for code formatting and style")).toBeInTheDocument();
  });

  it("should render rule state badge", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByText("Draft")).toBeInTheDocument();
  });

  it("should render published badge for published rule", () => {
    renderWithProviders(
      <RuleDetail {...defaultProps} rule={{ ...mockRule, state: "published" }} />,
    );

    expect(screen.getByText("Published")).toBeInTheDocument();
  });

  it("should render rule content when present", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByText("Content")).toBeInTheDocument();
    expect(
      screen.getByText("This is the rule content explaining the guidelines."),
    ).toBeInTheDocument();
  });

  it("should not render content section when content is null", () => {
    renderWithProviders(<RuleDetail {...defaultProps} rule={{ ...mockRule, content: null }} />);

    expect(screen.queryByText("Content")).not.toBeInTheDocument();
  });

  it("should render examples section with example count", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByText("Examples (1)")).toBeInTheDocument();
  });

  it("should render good and bad examples", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByText("Good")).toBeInTheDocument();
    expect(screen.getByText("const userName = 'John';")).toBeInTheDocument();
    expect(screen.getByText("Bad")).toBeInTheDocument();
    expect(screen.getByText("const user_name = 'John';")).toBeInTheDocument();
  });

  it("should render example explanation when present", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByText("Use camelCase for variable names")).toBeInTheDocument();
  });

  it("should render empty state for examples when no examples exist", () => {
    renderWithProviders(<RuleDetail {...defaultProps} rule={{ ...mockRule, examples: [] }} />);

    expect(screen.getByText("Examples (0)")).toBeInTheDocument();
    expect(
      screen.getByText("No examples defined yet. Add examples to demonstrate the rule in action."),
    ).toBeInTheDocument();
  });

  it("should render edit button for draft rule", () => {
    renderWithProviders(<RuleDetail {...defaultProps} />);

    expect(screen.getByRole("link", { name: /edit/i })).toBeInTheDocument();
  });

  it("should not render edit button for published rule", () => {
    renderWithProviders(
      <RuleDetail {...defaultProps} rule={{ ...mockRule, state: "published" }} />,
    );

    expect(screen.queryByRole("link", { name: /edit/i })).not.toBeInTheDocument();
  });

  it("should not render edit button for archived rule", () => {
    renderWithProviders(<RuleDetail {...defaultProps} rule={{ ...mockRule, state: "archived" }} />);

    expect(screen.queryByRole("link", { name: /edit/i })).not.toBeInTheDocument();
  });

  it("should show loading state", () => {
    renderWithProviders(<RuleDetail rule={undefined} isLoading={true} isError={false} />);

    const loader = document.querySelector('[class*="Loader"]');
    expect(loader).toBeInTheDocument();
  });

  it("should show error state with message", () => {
    const error = new Error("Network request failed");
    renderWithProviders(
      <RuleDetail rule={undefined} isLoading={false} isError={true} error={error} />,
    );

    expect(screen.getByText("Error loading rule")).toBeInTheDocument();
    expect(screen.getByText("Network request failed")).toBeInTheDocument();
  });

  it("should show error state with default message when no error message", () => {
    renderWithProviders(
      <RuleDetail rule={undefined} isLoading={false} isError={true} error={null} />,
    );

    expect(screen.getByText("Error loading rule")).toBeInTheDocument();
    expect(screen.getByText("An unexpected error occurred")).toBeInTheDocument();
  });

  it("should show not found state when rule is undefined and not loading or error", () => {
    renderWithProviders(<RuleDetail rule={undefined} isLoading={false} isError={false} />);

    expect(screen.getByText("Rule not found")).toBeInTheDocument();
    expect(screen.getByText("The requested rule could not be found.")).toBeInTheDocument();
  });

  it("should render example with only good example", () => {
    const ruleWithGoodOnly: RuleResponse = {
      ...mockRule,
      examples: [
        {
          id: "example-2",
          good: "Good practice example",
          bad: null,
          explanation: null,
          createdAt: new Date("2025-01-01T00:00:00Z"),
          updatedAt: new Date("2025-01-01T00:00:00Z"),
        },
      ],
    };

    renderWithProviders(<RuleDetail {...defaultProps} rule={ruleWithGoodOnly} />);

    expect(screen.getByText("Good")).toBeInTheDocument();
    expect(screen.getByText("Good practice example")).toBeInTheDocument();
    expect(screen.queryByText("Bad")).not.toBeInTheDocument();
  });

  it("should render example with only bad example", () => {
    const ruleWithBadOnly: RuleResponse = {
      ...mockRule,
      examples: [
        {
          id: "example-3",
          good: null,
          bad: "Bad practice example",
          explanation: null,
          createdAt: new Date("2025-01-01T00:00:00Z"),
          updatedAt: new Date("2025-01-01T00:00:00Z"),
        },
      ],
    };

    renderWithProviders(<RuleDetail {...defaultProps} rule={ruleWithBadOnly} />);

    expect(screen.queryByText("Good")).not.toBeInTheDocument();
    expect(screen.getByText("Bad")).toBeInTheDocument();
    expect(screen.getByText("Bad practice example")).toBeInTheDocument();
  });
});
