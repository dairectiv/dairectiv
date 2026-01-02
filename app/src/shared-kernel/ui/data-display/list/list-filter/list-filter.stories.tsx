import { Group } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { fn } from "@storybook/test";
import { ListFilter } from "./list-filter";

const meta: Meta<typeof ListFilter> = {
  title: "Data Display/List/ListFilter",
  component: ListFilter,
  tags: ["autodocs"],
  args: {
    onChange: fn(),
  },
};

export default meta;
type Story = StoryObj<typeof ListFilter>;

const stateOptions = [
  { value: "draft", label: "Draft" },
  { value: "published", label: "Published" },
  { value: "archived", label: "Archived" },
];

const typeOptions = [
  { value: "feature", label: "Feature" },
  { value: "bugfix", label: "Bugfix" },
  { value: "improvement", label: "Improvement" },
  { value: "chore", label: "Chore" },
];

export const Default: Story = {
  args: {
    options: stateOptions,
    placeholder: "Filter by state",
  },
};

export const WithValue: Story = {
  args: {
    value: "draft",
    options: stateOptions,
    placeholder: "Filter by state",
  },
};

export const NotClearable: Story = {
  args: {
    value: "feature",
    options: typeOptions,
    placeholder: "Filter by type",
    clearable: false,
  },
};

export const CustomWidth: Story = {
  args: {
    options: typeOptions,
    placeholder: "Type",
    width: 200,
  },
};

export const MultipleFilters: Story = {
  render: () => (
    <Group gap="sm">
      <ListFilter options={stateOptions} placeholder="State" onChange={() => {}} />
      <ListFilter options={typeOptions} placeholder="Type" onChange={() => {}} />
    </Group>
  ),
};
