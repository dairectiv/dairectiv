import { Box } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { IconAdjustments, IconBook, IconGauge, IconNotes, IconUsers } from "@tabler/icons-react";
import { Navbar } from "./navbar";

const meta: Meta<typeof Navbar> = {
  title: "Navigation/Navbar",
  component: Navbar,
  tags: ["autodocs"],
  parameters: {
    layout: "fullscreen",
  },
  decorators: [
    (Story) => (
      <Box style={{ height: "100vh" }}>
        <Story />
      </Box>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof Navbar>;

export const Default: Story = {};

export const WithVersion: Story = {
  args: {
    version: "1.2.3",
  },
};

export const WithCustomUser: Story = {
  args: {
    version: "1.0.0",
    user: {
      name: "Jane Smith",
      email: "jane.smith@dairectiv.com",
      image:
        "https://raw.githubusercontent.com/mantinedev/mantine/master/.demo/avatars/avatar-8.png",
    },
  },
};

export const WithCustomLinks: Story = {
  args: {
    version: "1.0.0",
    links: [
      { label: "Dashboard", icon: IconGauge, link: "/" },
      {
        label: "Authoring",
        icon: IconNotes,
        initiallyOpened: true,
        links: [
          { label: "Rules", link: "/authoring/rules" },
          { label: "Skills", link: "/authoring/skills" },
          { label: "Workflows", link: "/authoring/workflows" },
          { label: "Agents", link: "/authoring/agents" },
        ],
      },
      {
        label: "Documentation",
        icon: IconBook,
        links: [
          { label: "Getting Started", link: "/docs/getting-started" },
          { label: "API Reference", link: "/docs/api" },
          { label: "Examples", link: "/docs/examples" },
        ],
      },
      { label: "Team", icon: IconUsers, link: "/team" },
      { label: "Settings", icon: IconAdjustments, link: "/settings" },
    ],
    user: {
      name: "Admin User",
      email: "admin@dairectiv.com",
    },
  },
};

export const ManyLinks: Story = {
  args: {
    version: "2.0.0-beta",
    links: [
      { label: "Dashboard", icon: IconGauge, link: "/" },
      {
        label: "Authoring",
        icon: IconNotes,
        initiallyOpened: true,
        links: [
          { label: "Rules", link: "/authoring/rules" },
          { label: "Skills", link: "/authoring/skills" },
          { label: "Workflows", link: "/authoring/workflows" },
          { label: "Agents", link: "/authoring/agents" },
          { label: "Templates", link: "/authoring/templates" },
          { label: "Snippets", link: "/authoring/snippets" },
        ],
      },
      {
        label: "Documentation",
        icon: IconBook,
        links: [
          { label: "Getting Started", link: "/docs/getting-started" },
          { label: "Installation", link: "/docs/installation" },
          { label: "Configuration", link: "/docs/configuration" },
          { label: "API Reference", link: "/docs/api" },
          { label: "Examples", link: "/docs/examples" },
          { label: "FAQ", link: "/docs/faq" },
          { label: "Troubleshooting", link: "/docs/troubleshooting" },
        ],
      },
      {
        label: "Administration",
        icon: IconUsers,
        links: [
          { label: "Users", link: "/admin/users" },
          { label: "Teams", link: "/admin/teams" },
          { label: "Roles", link: "/admin/roles" },
          { label: "Audit Log", link: "/admin/audit" },
        ],
      },
      { label: "Settings", icon: IconAdjustments, link: "/settings" },
    ],
    user: {
      name: "Power User",
      email: "power.user@dairectiv.com",
    },
  },
};
