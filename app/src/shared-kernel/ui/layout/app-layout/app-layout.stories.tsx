import { Text, Title } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { IconAdjustments, IconBook, IconGauge, IconNotes, IconUsers } from "@tabler/icons-react";
import { AppLayout } from "./app-layout";

const meta: Meta<typeof AppLayout> = {
  title: "Layout/AppLayout",
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
      <Title order={2} mb="lg">
        Dashboard
      </Title>
      <Text>Welcome to dairectiv. Main content goes here.</Text>
    </AppLayout>
  ),
};

export const WithCustomNavbar: Story = {
  render: () => (
    <AppLayout
      navbarProps={{
        version: "1.0.0",
        links: [
          { label: "Dashboard", icon: IconGauge, link: "/" },
          {
            label: "Authoring",
            icon: IconNotes,
            initiallyOpened: true,
            links: [
              { label: "Rules", link: "/authoring/rules" },
              { label: "Workflows", link: "/authoring/workflows" },
            ],
          },
          {
            label: "Documentation",
            icon: IconBook,
            links: [
              { label: "Getting Started", link: "/docs/getting-started" },
              { label: "API Reference", link: "/docs/api" },
            ],
          },
          { label: "Team", icon: IconUsers, link: "/team" },
          { label: "Settings", icon: IconAdjustments, link: "/settings" },
        ],
        user: {
          name: "John Doe",
          email: "john.doe@dairectiv.com",
          image:
            "https://raw.githubusercontent.com/mantinedev/mantine/master/.demo/avatars/avatar-8.png",
        },
      }}
    >
      <Title order={2} mb="lg">
        Custom Navigation
      </Title>
      <Text>This layout has a customized navbar.</Text>
    </AppLayout>
  ),
};

export const WithLongContent: Story = {
  render: () => (
    <AppLayout>
      <Title order={2} mb="lg">
        Long Content Page
      </Title>
      <div>
        {Array.from({ length: 30 }).map((_, i) => (
          <Text key={`content-${i.toString()}`} mb="md">
            Content paragraph {i + 1}. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed
            do eiusmod tempor incididunt ut labore et dolore magna aliqua.
          </Text>
        ))}
      </div>
    </AppLayout>
  ),
};
