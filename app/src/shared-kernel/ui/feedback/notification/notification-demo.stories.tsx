import { Button, Group, Stack, Text, Title } from "@mantine/core";
import type { Meta, StoryObj } from "@storybook/react";
import {
  hideAllNotifications,
  showError,
  showInfo,
  showLoadingNotification,
  showSuccess,
  showWarning,
  updateToError,
  updateToSuccess,
} from "./notification-helpers";

/**
 * Interactive demo component for notification helpers.
 * Click the buttons to see different notification types.
 */
function NotificationDemo() {
  const handleShowSuccess = () => {
    showSuccess({
      title: "Rule created",
      message: "Your rule has been created successfully.",
    });
  };

  const handleShowError = () => {
    showError({
      title: "Error creating rule",
      message: "A rule with this name already exists. Please choose a different name.",
    });
  };

  const handleShowInfo = () => {
    showInfo({
      title: "Information",
      message: "This workflow has been shared with your team.",
    });
  };

  const handleShowWarning = () => {
    showWarning({
      title: "Warning",
      message: "You have unsaved changes. Are you sure you want to leave?",
    });
  };

  const handleLoadingSuccess = () => {
    const id = showLoadingNotification({
      title: "Creating rule",
      message: "Rule created successfully",
      loadingMessage: "Creating your rule...",
    });

    // Simulate API call
    setTimeout(() => {
      updateToSuccess(id, {
        title: "Rule created",
        message: "Your rule has been created successfully.",
      });
    }, 2000);
  };

  const handleLoadingError = () => {
    const id = showLoadingNotification({
      title: "Saving workflow",
      message: "Error message",
      loadingMessage: "Saving your workflow...",
    });

    // Simulate API call failure
    setTimeout(() => {
      updateToError(id, {
        title: "Error saving workflow",
        message: "Failed to save. Please try again.",
      });
    }, 2000);
  };

  const handleClearAll = () => {
    hideAllNotifications();
  };

  return (
    <Stack gap="xl" p="md">
      <div>
        <Title order={3}>Notification System Demo</Title>
        <Text c="dimmed" size="sm">
          Click the buttons below to see different notification types in action.
        </Text>
      </div>

      <Stack gap="md">
        <div>
          <Text fw={500} mb="xs">
            Direct Notifications
          </Text>
          <Group>
            <Button color="green" onClick={handleShowSuccess}>
              Show Success
            </Button>
            <Button color="red" onClick={handleShowError}>
              Show Error
            </Button>
            <Button color="blue" onClick={handleShowInfo}>
              Show Info
            </Button>
            <Button color="yellow" onClick={handleShowWarning}>
              Show Warning
            </Button>
          </Group>
        </div>

        <div>
          <Text fw={500} mb="xs">
            Loading → Result Pattern
          </Text>
          <Text c="dimmed" size="sm" mb="xs">
            These simulate an API call with loading state, then update to success or error.
          </Text>
          <Group>
            <Button variant="light" color="green" onClick={handleLoadingSuccess}>
              Loading → Success (2s)
            </Button>
            <Button variant="light" color="red" onClick={handleLoadingError}>
              Loading → Error (2s)
            </Button>
          </Group>
        </div>

        <div>
          <Text fw={500} mb="xs">
            Utilities
          </Text>
          <Group>
            <Button variant="outline" color="gray" onClick={handleClearAll}>
              Clear All Notifications
            </Button>
          </Group>
        </div>
      </Stack>
    </Stack>
  );
}

const meta: Meta<typeof NotificationDemo> = {
  title: "Feedback/Notification/Demo",
  component: NotificationDemo,
  tags: ["autodocs"],
  parameters: {
    layout: "fullscreen",
  },
};

export default meta;
type Story = StoryObj<typeof NotificationDemo>;

export const Default: Story = {};
