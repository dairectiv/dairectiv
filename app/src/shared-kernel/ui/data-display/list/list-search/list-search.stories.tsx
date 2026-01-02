import type { Meta, StoryObj } from "@storybook/react";
import { fn } from "@storybook/test";
import { ListSearch } from "./list-search";

const meta: Meta<typeof ListSearch> = {
  title: "Data Display/ListSearch",
  component: ListSearch,
  tags: ["autodocs"],
  args: {
    onChange: fn(),
  },
};

export default meta;
type Story = StoryObj<typeof ListSearch>;

export const Default: Story = {
  args: {
    placeholder: "Search...",
  },
};

export const WithValue: Story = {
  args: {
    value: "typescript",
    placeholder: "Search rules...",
  },
};

export const CustomPlaceholder: Story = {
  args: {
    placeholder: "Search by name, description...",
  },
};

export const NarrowWidth: Story = {
  args: {
    placeholder: "Search...",
    maxWidth: 200,
  },
};
