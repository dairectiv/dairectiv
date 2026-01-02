import type { Meta, StoryObj } from "@storybook/react";
import { StateBadge } from "./state-badge";

const meta: Meta<typeof StateBadge> = {
  title: "Data Display/StateBadge",
  component: StateBadge,
  tags: ["autodocs"],
  argTypes: {
    state: {
      control: "select",
      options: ["draft", "published", "archived", "deleted"],
    },
    size: {
      control: "select",
      options: ["xs", "sm", "md", "lg", "xl"],
    },
  },
};

export default meta;
type Story = StoryObj<typeof StateBadge>;

export const Draft: Story = {
  args: {
    state: "draft",
  },
};

export const Published: Story = {
  args: {
    state: "published",
  },
};

export const Archived: Story = {
  args: {
    state: "archived",
  },
};

export const Deleted: Story = {
  args: {
    state: "deleted",
  },
};

export const AllStates: Story = {
  render: () => (
    <div style={{ display: "flex", gap: "8px" }}>
      <StateBadge state="draft" />
      <StateBadge state="published" />
      <StateBadge state="archived" />
      <StateBadge state="deleted" />
    </div>
  ),
};

export const Sizes: Story = {
  render: () => (
    <div style={{ display: "flex", gap: "8px", alignItems: "center" }}>
      <StateBadge state="published" size="xs" />
      <StateBadge state="published" size="sm" />
      <StateBadge state="published" size="md" />
      <StateBadge state="published" size="lg" />
      <StateBadge state="published" size="xl" />
    </div>
  ),
};
