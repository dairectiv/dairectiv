import { Button } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { IconFileOff, IconInbox, IconSearch } from "@tabler/icons-react";
import { ListEmpty } from "./list-empty";

const meta: Meta<typeof ListEmpty> = {
  title: "Data Display/List/ListEmpty",
  component: ListEmpty,
  tags: ["autodocs"],
};

export default meta;
type Story = StoryObj<typeof ListEmpty>;

export const Default: Story = {
  args: {
    title: "No items found",
  },
};

export const WithSubtitle: Story = {
  args: {
    title: "No rules found",
    subtitle: "Create your first rule to get started",
  },
};

export const WithAction: Story = {
  args: {
    title: "No rules found",
    subtitle: "Create your first rule to get started",
    action: <Button size="sm">Create Rule</Button>,
  },
};

export const CustomIcon: Story = {
  args: {
    icon: IconFileOff,
    title: "No documents",
    subtitle: "Upload a document to get started",
  },
};

export const SearchNoResults: Story = {
  args: {
    icon: IconSearch,
    title: "No results found",
    subtitle: "Try adjusting your search or filter criteria",
  },
};

export const InboxEmpty: Story = {
  args: {
    icon: IconInbox,
    title: "Inbox is empty",
    subtitle: "You're all caught up!",
  },
};
