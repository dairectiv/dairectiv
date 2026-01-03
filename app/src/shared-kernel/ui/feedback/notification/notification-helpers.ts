import { notifications } from "@mantine/notifications";

export interface NotificationOptions {
  title: string;
  message: string;
}

export interface LoadingNotificationOptions extends NotificationOptions {
  loadingMessage?: string;
}

/**
 * Shows a loading notification that can be updated to success or error.
 * Returns an ID that can be used to update the notification.
 */
export function showLoadingNotification(options: LoadingNotificationOptions): string {
  const id = crypto.randomUUID();

  notifications.show({
    id,
    title: options.title,
    message: options.loadingMessage ?? "Please wait...",
    loading: true,
    autoClose: false,
    withCloseButton: false,
  });

  return id;
}

/**
 * Updates a loading notification to show success.
 */
export function updateToSuccess(id: string, options: NotificationOptions): void {
  notifications.update({
    id,
    title: options.title,
    message: options.message,
    color: "green",
    loading: false,
    autoClose: 5000,
    withCloseButton: true,
  });
}

/**
 * Updates a loading notification to show error.
 */
export function updateToError(id: string, options: NotificationOptions): void {
  notifications.update({
    id,
    title: options.title,
    message: options.message,
    color: "red",
    loading: false,
    autoClose: false,
    withCloseButton: true,
  });
}

/**
 * Shows a success notification directly (without loading state).
 */
export function showSuccess(options: NotificationOptions): void {
  notifications.show({
    title: options.title,
    message: options.message,
    color: "green",
    autoClose: 5000,
  });
}

/**
 * Shows an error notification directly (without loading state).
 */
export function showError(options: NotificationOptions): void {
  notifications.show({
    title: options.title,
    message: options.message,
    color: "red",
    autoClose: false,
  });
}

/**
 * Shows an info notification.
 */
export function showInfo(options: NotificationOptions): void {
  notifications.show({
    title: options.title,
    message: options.message,
    color: "blue",
    autoClose: 5000,
  });
}

/**
 * Shows a warning notification.
 */
export function showWarning(options: NotificationOptions): void {
  notifications.show({
    title: options.title,
    message: options.message,
    color: "yellow",
    autoClose: 8000,
  });
}

/**
 * Hides a notification by ID.
 */
export function hideNotification(id: string): void {
  notifications.hide(id);
}

/**
 * Hides all notifications.
 */
export function hideAllNotifications(): void {
  notifications.cleanQueue();
  notifications.clean();
}
