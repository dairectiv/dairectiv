import { Text } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { AppLayout } from "./app-layout";

const meta: Meta<typeof AppLayout> = {
  title: "Layouts/AppLayout",
  component: AppLayout,
  parameters: {
    layout: "fullscreen",
  },
};

export default meta;
type Story = StoryObj<typeof AppLayout>;

export const Default: Story = {
  render: () => (
    <AppLayout>
      <Text>Main content goes here</Text>
    </AppLayout>
  ),
};

export const WithLongContent: Story = {
  render: () => (
    <AppLayout>
      <div>
        {Array.from({ length: 50 }).map((_, i) => (
          <Text key={`content-line-${i}`} mb="md">
            Content line {i + 1}
          </Text>
        ))}
      </div>
    </AppLayout>
  ),
};
