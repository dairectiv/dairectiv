import { Stack } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { DirectiveListItem } from "./directive-list-item";

const meta: Meta<typeof DirectiveListItem> = {
  title: "Data Display/DirectiveListItem",
  component: DirectiveListItem,
  tags: ["autodocs"],
  argTypes: {
    state: {
      control: "select",
      options: ["draft", "published", "archived", "deleted"],
    },
  },
};

export default meta;
type Story = StoryObj<typeof DirectiveListItem>;

export const Draft: Story = {
  args: {
    name: "REST API Resource Naming",
    description: "Use plural nouns for REST API resource endpoints to maintain consistency.",
    state: "draft",
  },
};

export const Published: Story = {
  args: {
    name: "Error Handling Best Practices",
    description:
      "Always use try-catch blocks for async operations and provide meaningful error messages to users.",
    state: "published",
  },
};

export const Archived: Story = {
  args: {
    name: "Legacy Authentication Flow",
    description: "This authentication method has been deprecated in favor of OAuth 2.0.",
    state: "archived",
  },
};

export const LongDescription: Story = {
  args: {
    name: "Comprehensive Testing Guidelines",
    description:
      "Write unit tests for all business logic, integration tests for API endpoints, and end-to-end tests for critical user flows. Ensure test coverage remains above 80% and all tests are maintainable and readable.",
    state: "published",
  },
};

export const Clickable: Story = {
  args: {
    name: "Clickable Item",
    description: "Click this item to trigger an action.",
    state: "draft",
    onClick: () => alert("Item clicked!"),
  },
};

export const List: Story = {
  render: () => (
    <Stack gap="xs">
      <DirectiveListItem
        name="REST API Resource Naming"
        description="Use plural nouns for REST API resource endpoints."
        state="published"
        onClick={() => {}}
      />
      <DirectiveListItem
        name="Error Handling Best Practices"
        description="Always use try-catch blocks for async operations."
        state="published"
        onClick={() => {}}
      />
      <DirectiveListItem
        name="Database Migration Guidelines"
        description="Create reversible migrations with clear up/down methods."
        state="draft"
        onClick={() => {}}
      />
      <DirectiveListItem
        name="Legacy Code Patterns"
        description="Deprecated patterns that should no longer be used."
        state="archived"
        onClick={() => {}}
      />
    </Stack>
  ),
};
