import { Paper, Text } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { fn } from "@storybook/test";
import { IconFileOff } from "@tabler/icons-react";
import { ListContainer } from "./list-container";

const meta: Meta<typeof ListContainer> = {
  title: "Data Display/List/ListContainer",
  component: ListContainer,
  tags: ["autodocs"],
};

export default meta;
type Story = StoryObj<typeof ListContainer>;

const SampleItem = ({ title }: { title: string }) => (
  <Paper p="md" withBorder>
    <Text>{title}</Text>
  </Paper>
);

export const Default: Story = {
  args: {
    children: (
      <>
        <SampleItem title="Item 1" />
        <SampleItem title="Item 2" />
        <SampleItem title="Item 3" />
      </>
    ),
  },
};

export const Loading: Story = {
  args: {
    isLoading: true,
    children: null,
  },
};

export const ErrorState: Story = {
  args: {
    isError: true,
    errorMessage: "Failed to load rules",
    errorDetails: "Network request failed",
    children: null,
  },
};

export const Empty: Story = {
  args: {
    isEmpty: true,
    empty: {
      title: "No items found",
      subtitle: "Create your first item to get started",
    },
    children: null,
  },
};

export const EmptyWithCustomIcon: Story = {
  args: {
    isEmpty: true,
    empty: {
      icon: IconFileOff,
      title: "No documents",
      subtitle: "Upload a document to get started",
    },
    children: null,
  },
};

export const WithPagination: Story = {
  args: {
    children: (
      <>
        <SampleItem title="Item 1" />
        <SampleItem title="Item 2" />
        <SampleItem title="Item 3" />
        <SampleItem title="Item 4" />
        <SampleItem title="Item 5" />
      </>
    ),
    pagination: {
      page: 1,
      totalPages: 5,
      total: 50,
    },
    itemCount: 5,
    itemLabel: "items",
    onPageChange: fn(),
  },
};

export const PaginationPage3: Story = {
  args: {
    children: (
      <>
        <SampleItem title="Item 21" />
        <SampleItem title="Item 22" />
        <SampleItem title="Item 23" />
        <SampleItem title="Item 24" />
        <SampleItem title="Item 25" />
      </>
    ),
    pagination: {
      page: 3,
      totalPages: 5,
      total: 50,
    },
    itemCount: 5,
    itemLabel: "rules",
    onPageChange: fn(),
  },
};

export const SinglePage: Story = {
  args: {
    children: (
      <>
        <SampleItem title="Item 1" />
        <SampleItem title="Item 2" />
        <SampleItem title="Item 3" />
      </>
    ),
    pagination: {
      page: 1,
      totalPages: 1,
      total: 3,
    },
    itemCount: 3,
    itemLabel: "items",
  },
};
