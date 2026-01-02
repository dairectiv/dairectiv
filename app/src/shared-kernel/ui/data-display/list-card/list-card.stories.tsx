import { Badge, Stack } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { ListCard } from "./list-card";

const meta: Meta<typeof ListCard> = {
  title: "Data Display/ListCard",
  component: ListCard,
  tags: ["autodocs"],
};

export default meta;
type Story = StoryObj<typeof ListCard>;

export const Default: Story = {
  args: {
    title: "REST API Resource Naming",
    description: "Use plural nouns for REST API resource endpoints to maintain consistency.",
  },
};

export const WithMetadata: Story = {
  args: {
    title: "Error Handling Best Practices",
    description:
      "Always use try-catch blocks for async operations and provide meaningful error messages.",
    metadata: "3 days ago",
    badge: (
      <Badge color="green" variant="light" size="sm">
        Published
      </Badge>
    ),
  },
};

export const WithoutDescription: Story = {
  args: {
    title: "Simple Item",
    metadata: "12 hours ago",
    badge: (
      <Badge color="blue" variant="light" size="sm">
        Active
      </Badge>
    ),
  },
};

export const LongDescription: Story = {
  args: {
    title: "Comprehensive Testing Guidelines",
    description:
      "Write unit tests for all business logic, integration tests for API endpoints, and end-to-end tests for critical user flows. Ensure test coverage remains above 80% and all tests are maintainable and readable.",
    metadata: "2 weeks ago",
    badge: (
      <Badge color="yellow" variant="light" size="sm">
        Draft
      </Badge>
    ),
  },
};

export const Clickable: Story = {
  args: {
    title: "Clickable Item",
    description: "Click this item to trigger an action.",
    metadata: "just now",
    onClick: () => alert("Item clicked!"),
  },
};

export const List: Story = {
  render: () => (
    <Stack gap="xs">
      <ListCard
        title="REST API Resource Naming"
        description="Use plural nouns for REST API resource endpoints."
        metadata="3 days ago"
        badge={
          <Badge color="green" variant="light" size="sm">
            Published
          </Badge>
        }
        onClick={() => {}}
      />
      <ListCard
        title="Error Handling Best Practices"
        description="Always use try-catch blocks for async operations."
        metadata="1 week ago"
        badge={
          <Badge color="green" variant="light" size="sm">
            Published
          </Badge>
        }
        onClick={() => {}}
      />
      <ListCard
        title="Database Migration Guidelines"
        description="Create reversible migrations with clear up/down methods."
        metadata="2 hours ago"
        badge={
          <Badge color="yellow" variant="light" size="sm">
            Draft
          </Badge>
        }
        onClick={() => {}}
      />
      <ListCard
        title="Legacy Code Patterns"
        description="Deprecated patterns that should no longer be used."
        metadata="3 months ago"
        badge={
          <Badge color="gray" variant="light" size="sm">
            Archived
          </Badge>
        }
        onClick={() => {}}
      />
    </Stack>
  ),
};
