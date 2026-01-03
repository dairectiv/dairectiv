import { Notifications } from "@mantine/notifications";

export interface NotificationContainerProps {
  /** Position of the notifications container */
  position?:
    | "top-left"
    | "top-right"
    | "bottom-left"
    | "bottom-right"
    | "top-center"
    | "bottom-center";
  /** Auto close delay in milliseconds */
  autoClose?: number;
  /** Maximum number of notifications displayed at once */
  limit?: number;
}

/**
 * Container component for displaying notifications.
 * This is a wrapper around Mantine's Notifications component with project defaults.
 *
 * Place this component once at the root of your application (e.g., in router.tsx).
 * Notifications are displayed at the bottom-right by default and stack from bottom to top.
 */
export function NotificationContainer({
  position = "bottom-right",
  autoClose = 5000,
  limit = 5,
}: NotificationContainerProps) {
  return <Notifications position={position} autoClose={autoClose} limit={limit} />;
}
