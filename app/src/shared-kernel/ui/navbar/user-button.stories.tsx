import type { Meta, StoryObj } from "@storybook/react";
import { UserButton } from "./user-button";

const meta: Meta<typeof UserButton> = {
  title: "Navigation/UserButton",
  component: UserButton,
  parameters: {
    layout: "centered",
  },
  args: {
    name: "John Doe",
    email: "john.doe@example.com",
  },
};

export default meta;
type Story = StoryObj<typeof UserButton>;

export const Default: Story = {};

export const WithAvatar: Story = {
  args: {
    image: "https://raw.githubusercontent.com/mantinedev/mantine/master/.demo/avatars/avatar-8.png",
    name: "Jane Smith",
    email: "jane.smith@example.com",
  },
};

export const LongName: Story = {
  args: {
    name: "Alexander Constantine Richardson III",
    email: "alexander.constantine.richardson@verylongdomain.example.com",
  },
};

export const WithClickHandler: Story = {
  args: {
    name: "Click Me",
    email: "click@example.com",
    onClick: () => alert("User button clicked!"),
  },
};
