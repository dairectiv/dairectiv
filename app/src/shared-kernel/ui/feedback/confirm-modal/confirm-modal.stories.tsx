import { Button, Stack } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type { Meta, StoryObj } from "@storybook/react";
import { ConfirmModal } from "./confirm-modal";

const meta: Meta<typeof ConfirmModal> = {
  title: "Feedback/ConfirmModal",
  component: ConfirmModal,
  tags: ["autodocs"],
  argTypes: {
    confirmColor: {
      control: "select",
      options: ["blue", "green", "orange", "red", "gray"],
    },
    isLoading: {
      control: "boolean",
    },
  },
};

export default meta;
type Story = StoryObj<typeof ConfirmModal>;

export const Default: Story = {
  args: {
    opened: true,
    title: "Confirm Action",
    message: "Are you sure you want to proceed with this action?",
    confirmLabel: "Confirm",
    cancelLabel: "Cancel",
    confirmColor: "blue",
    isLoading: false,
    onClose: () => {},
    onConfirm: () => {},
  },
};

export const ArchiveAction: Story = {
  args: {
    opened: true,
    title: "Archive Directive",
    message:
      "Are you sure you want to archive this directive? It will no longer be visible to AI tools and cannot be edited. You can restore it later from the archived items.",
    confirmLabel: "Archive",
    cancelLabel: "Cancel",
    confirmColor: "orange",
    isLoading: false,
    onClose: () => {},
    onConfirm: () => {},
  },
};

export const DeleteAction: Story = {
  args: {
    opened: true,
    title: "Delete Item",
    message: "Are you sure you want to delete this item? This action cannot be undone.",
    confirmLabel: "Delete",
    cancelLabel: "Cancel",
    confirmColor: "red",
    isLoading: false,
    onClose: () => {},
    onConfirm: () => {},
  },
};

export const PublishAction: Story = {
  args: {
    opened: true,
    title: "Publish Directive",
    message:
      "Are you sure you want to publish this directive? Once published, it will be visible to AI tools.",
    confirmLabel: "Publish",
    cancelLabel: "Cancel",
    confirmColor: "green",
    isLoading: false,
    onClose: () => {},
    onConfirm: () => {},
  },
};

export const Loading: Story = {
  args: {
    opened: true,
    title: "Archive Directive",
    message: "Are you sure you want to archive this directive?",
    confirmLabel: "Archive",
    cancelLabel: "Cancel",
    confirmColor: "orange",
    isLoading: true,
    onClose: () => {},
    onConfirm: () => {},
  },
};

function InteractiveDemo() {
  const [opened, { open, close }] = useDisclosure(false);

  return (
    <Stack>
      <Button onClick={open}>Open Confirmation Modal</Button>
      <ConfirmModal
        opened={opened}
        onClose={close}
        onConfirm={() => {
          // Simulate async action
          setTimeout(close, 500);
        }}
        title="Archive Directive"
        message="Are you sure you want to archive this directive? It will no longer be visible to AI tools."
        confirmLabel="Archive"
        confirmColor="orange"
      />
    </Stack>
  );
}

export const Interactive: Story = {
  render: () => <InteractiveDemo />,
};
