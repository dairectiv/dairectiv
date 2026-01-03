import { Notification, type NotificationProps } from "@mantine/core";
import { IconAlertTriangle, IconCheck, IconInfoCircle, IconX } from "@tabler/icons-react";
import type { ReactNode } from "react";

export type NotificationVariant = "success" | "error" | "info" | "warning";

export interface NotificationItemProps extends Omit<NotificationProps, "icon" | "color"> {
  /** The variant determines the color and icon */
  variant: NotificationVariant;
  /** Override the default icon for this variant */
  icon?: ReactNode;
}

const variantConfig: Record<NotificationVariant, { color: string; icon: ReactNode }> = {
  success: {
    color: "green",
    icon: <IconCheck size={18} />,
  },
  error: {
    color: "red",
    icon: <IconX size={18} />,
  },
  info: {
    color: "blue",
    icon: <IconInfoCircle size={18} />,
  },
  warning: {
    color: "yellow",
    icon: <IconAlertTriangle size={18} />,
  },
};

/**
 * A styled notification item component.
 * This is a wrapper around Mantine's Notification component with predefined variants.
 *
 * Note: For most use cases, use the notification helper functions (showSuccess, showError, etc.)
 * which automatically display notifications via the NotificationContainer.
 * This component is useful when you need to render a notification inline (e.g., in Storybook).
 */
export function NotificationItem({ variant, icon, ...props }: NotificationItemProps) {
  const config = variantConfig[variant];

  return <Notification color={config.color} icon={icon ?? config.icon} {...props} />;
}
