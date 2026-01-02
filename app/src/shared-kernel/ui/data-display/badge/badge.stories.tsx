import { Group, Stack } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { Badge } from "./badge";

const meta: Meta<typeof Badge> = {
  title: "Data Display/Badge",
  component: Badge,
  tags: ["autodocs"],
  argTypes: {
    color: {
      control: "select",
      options: [
        "gray",
        "red",
        "pink",
        "grape",
        "violet",
        "indigo",
        "blue",
        "cyan",
        "teal",
        "green",
        "lime",
        "yellow",
        "orange",
      ],
    },
    size: {
      control: "select",
      options: ["xs", "sm", "md", "lg", "xl"],
    },
  },
};

export default meta;
type Story = StoryObj<typeof Badge>;

export const Default: Story = {
  args: {
    label: "Badge",
    color: "blue",
  },
};

export const Colors: Story = {
  render: () => (
    <Group gap="xs">
      <Badge label="Draft" color="yellow" />
      <Badge label="Published" color="green" />
      <Badge label="Archived" color="gray" />
      <Badge label="Deleted" color="red" />
      <Badge label="Info" color="blue" />
      <Badge label="Warning" color="orange" />
    </Group>
  ),
};

export const Sizes: Story = {
  render: () => (
    <Group gap="xs" align="center">
      <Badge label="XS" color="blue" size="xs" />
      <Badge label="SM" color="blue" size="sm" />
      <Badge label="MD" color="blue" size="md" />
      <Badge label="LG" color="blue" size="lg" />
      <Badge label="XL" color="blue" size="xl" />
    </Group>
  ),
};

export const AllVariants: Story = {
  render: () => (
    <Stack gap="md">
      <Group gap="xs">
        <Badge label="Draft" color="yellow" size="sm" />
        <Badge label="Published" color="green" size="sm" />
        <Badge label="Archived" color="gray" size="sm" />
        <Badge label="Deleted" color="red" size="sm" />
      </Group>
      <Group gap="xs">
        <Badge label="Feature" color="violet" size="sm" />
        <Badge label="Bugfix" color="red" size="sm" />
        <Badge label="Enhancement" color="cyan" size="sm" />
        <Badge label="Documentation" color="grape" size="sm" />
      </Group>
    </Stack>
  ),
};
