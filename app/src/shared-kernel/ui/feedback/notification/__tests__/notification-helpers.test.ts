import { notifications } from "@mantine/notifications";
import { beforeEach, describe, expect, it, vi } from "vitest";

// Mock @mantine/notifications
vi.mock("@mantine/notifications", () => ({
  notifications: {
    show: vi.fn(),
    update: vi.fn(),
    hide: vi.fn(),
    clean: vi.fn(),
    cleanQueue: vi.fn(),
  },
}));

// Mock crypto.randomUUID
vi.stubGlobal("crypto", {
  randomUUID: vi.fn(() => "test-uuid-123"),
});

import {
  hideAllNotifications,
  hideNotification,
  showError,
  showInfo,
  showLoadingNotification,
  showSuccess,
  showWarning,
  updateToError,
  updateToSuccess,
} from "../notification-helpers";

describe("notification-helpers", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  describe("showLoadingNotification", () => {
    it("should show a loading notification and return an ID", () => {
      const id = showLoadingNotification({
        title: "Creating rule",
        message: "Success message",
        loadingMessage: "Creating your rule...",
      });

      expect(id).toBe("test-uuid-123");
      expect(notifications.show).toHaveBeenCalledWith({
        id: "test-uuid-123",
        title: "Creating rule",
        message: "Creating your rule...",
        loading: true,
        autoClose: false,
        withCloseButton: false,
      });
    });

    it("should use default loading message when not provided", () => {
      showLoadingNotification({
        title: "Processing",
        message: "Done",
      });

      expect(notifications.show).toHaveBeenCalledWith(
        expect.objectContaining({
          message: "Please wait...",
        }),
      );
    });
  });

  describe("updateToSuccess", () => {
    it("should update notification to success state", () => {
      updateToSuccess("test-id", {
        title: "Rule created",
        message: "Your rule has been created successfully.",
      });

      expect(notifications.update).toHaveBeenCalledWith({
        id: "test-id",
        title: "Rule created",
        message: "Your rule has been created successfully.",
        color: "green",
        loading: false,
        autoClose: 5000,
        withCloseButton: true,
      });
    });
  });

  describe("updateToError", () => {
    it("should update notification to error state", () => {
      updateToError("test-id", {
        title: "Error",
        message: "Something went wrong.",
      });

      expect(notifications.update).toHaveBeenCalledWith({
        id: "test-id",
        title: "Error",
        message: "Something went wrong.",
        color: "red",
        loading: false,
        autoClose: false,
        withCloseButton: true,
      });
    });
  });

  describe("showSuccess", () => {
    it("should show a success notification", () => {
      showSuccess({
        title: "Success",
        message: "Operation completed.",
      });

      expect(notifications.show).toHaveBeenCalledWith({
        title: "Success",
        message: "Operation completed.",
        color: "green",
        autoClose: 5000,
      });
    });
  });

  describe("showError", () => {
    it("should show an error notification without auto-close", () => {
      showError({
        title: "Error",
        message: "Something went wrong.",
      });

      expect(notifications.show).toHaveBeenCalledWith({
        title: "Error",
        message: "Something went wrong.",
        color: "red",
        autoClose: false,
      });
    });
  });

  describe("showInfo", () => {
    it("should show an info notification", () => {
      showInfo({
        title: "Info",
        message: "Here is some information.",
      });

      expect(notifications.show).toHaveBeenCalledWith({
        title: "Info",
        message: "Here is some information.",
        color: "blue",
        autoClose: 5000,
      });
    });
  });

  describe("showWarning", () => {
    it("should show a warning notification with longer auto-close", () => {
      showWarning({
        title: "Warning",
        message: "Please be careful.",
      });

      expect(notifications.show).toHaveBeenCalledWith({
        title: "Warning",
        message: "Please be careful.",
        color: "yellow",
        autoClose: 8000,
      });
    });
  });

  describe("hideNotification", () => {
    it("should hide a notification by ID", () => {
      hideNotification("test-id");

      expect(notifications.hide).toHaveBeenCalledWith("test-id");
    });
  });

  describe("hideAllNotifications", () => {
    it("should clean queue and all notifications", () => {
      hideAllNotifications();

      expect(notifications.cleanQueue).toHaveBeenCalled();
      expect(notifications.clean).toHaveBeenCalled();
    });
  });
});
