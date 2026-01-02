import type { MantineColor } from "@mantine/core";
import { Button, Group, Modal, Stack, Text } from "@mantine/core";

export interface ConfirmModalProps {
  /** Whether the modal is open */
  opened: boolean;
  /** Function called when the modal is closed (cancel or outside click) */
  onClose: () => void;
  /** Function called when the confirm button is clicked */
  onConfirm: () => void;
  /** Modal title */
  title: string;
  /** Confirmation message explaining what will happen */
  message: string;
  /** Label for the confirm button */
  confirmLabel?: string;
  /** Label for the cancel button */
  cancelLabel?: string;
  /** Color for the confirm button (e.g., "red" for delete, "orange" for archive) */
  confirmColor?: MantineColor;
  /** Whether the confirm action is in progress */
  isLoading?: boolean;
}

export function ConfirmModal({
  opened,
  onClose,
  onConfirm,
  title,
  message,
  confirmLabel = "Confirm",
  cancelLabel = "Cancel",
  confirmColor = "blue",
  isLoading = false,
}: ConfirmModalProps) {
  return (
    <Modal opened={opened} onClose={onClose} title={title} centered>
      <Stack gap="lg">
        <Text size="sm">{message}</Text>
        <Group justify="flex-end" gap="sm">
          <Button variant="default" onClick={onClose} disabled={isLoading}>
            {cancelLabel}
          </Button>
          <Button color={confirmColor} onClick={onConfirm} loading={isLoading}>
            {confirmLabel}
          </Button>
        </Group>
      </Stack>
    </Modal>
  );
}
