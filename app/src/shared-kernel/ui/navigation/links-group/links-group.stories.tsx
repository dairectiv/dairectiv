import { Box } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import { IconGauge, IconNotes, IconSettings } from "@tabler/icons-react";
import { LinksGroup } from "./links-group";

const meta: Meta<typeof LinksGroup> = {
  title: "Navigation/LinksGroup",
  component: LinksGroup,
  tags: ["autodocs"],
  parameters: {
    layout: "centered",
  },
  decorators: [
    (Story) => (
      <Box style={{ width: 280, padding: 16 }}>
        <Story />
      </Box>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof LinksGroup>;

export const SimpleLink: Story = {
  args: {
    icon: IconGauge,
    label: "Dashboard",
    link: "/",
  },
};

export const WithNestedLinks: Story = {
  args: {
    icon: IconNotes,
    label: "Authoring",
    links: [
      { label: "Rules", link: "/authoring/rules" },
      { label: "Skills", link: "/authoring/skills" },
      { label: "Workflows", link: "/authoring/workflows" },
    ],
  },
};

export const InitiallyOpened: Story = {
  args: {
    icon: IconNotes,
    label: "Authoring",
    initiallyOpened: true,
    links: [
      { label: "Rules", link: "/authoring/rules" },
      { label: "Skills", link: "/authoring/skills" },
      { label: "Workflows", link: "/authoring/workflows" },
    ],
  },
};

export const SettingsLink: Story = {
  args: {
    icon: IconSettings,
    label: "Settings",
    link: "/settings",
  },
};

export const ManyNestedLinks: Story = {
  args: {
    icon: IconNotes,
    label: "Documentation",
    initiallyOpened: true,
    links: [
      { label: "Getting Started", link: "/docs/getting-started" },
      { label: "Installation", link: "/docs/installation" },
      { label: "Configuration", link: "/docs/configuration" },
      { label: "API Reference", link: "/docs/api" },
      { label: "Examples", link: "/docs/examples" },
      { label: "FAQ", link: "/docs/faq" },
    ],
  },
};
