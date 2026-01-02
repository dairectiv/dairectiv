import type { Meta, StoryObj } from "@storybook/react";
import { fn } from "@storybook/test";
import { ListSort } from "./list-sort";

const meta: Meta<typeof ListSort> = {
  title: "Data Display/List/ListSort",
  component: ListSort,
  tags: ["autodocs"],
  args: {
    onChange: fn(),
  },
};

export default meta;
type Story = StoryObj<typeof ListSort>;

const sortOptions = [
  { value: "updatedAt:desc", label: "Recently updated" },
  { value: "createdAt:desc", label: "Newest first" },
  { value: "createdAt:asc", label: "Oldest first" },
  { value: "name:asc", label: "Name A-Z" },
  { value: "name:desc", label: "Name Z-A" },
];

const simpleSortOptions = [
  { value: "date:desc", label: "Newest" },
  { value: "date:asc", label: "Oldest" },
  { value: "name:asc", label: "A-Z" },
  { value: "name:desc", label: "Z-A" },
];

export const Default: Story = {
  args: {
    value: "updatedAt:desc",
    options: sortOptions,
  },
};

export const NameSort: Story = {
  args: {
    value: "name:asc",
    options: sortOptions,
  },
};

export const SimpleOptions: Story = {
  args: {
    value: "date:desc",
    options: simpleSortOptions,
    width: 120,
  },
};

export const CustomWidth: Story = {
  args: {
    value: "updatedAt:desc",
    options: sortOptions,
    width: 220,
  },
};
