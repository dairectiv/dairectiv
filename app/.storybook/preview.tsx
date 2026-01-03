import { createTheme, MantineProvider } from "@mantine/core";
import "@mantine/core/styles.css";
import { Notifications } from "@mantine/notifications";
import "@mantine/notifications/styles.css";
import type { Preview, ReactRenderer } from "@storybook/react";
import type { PartialStoryFn } from "@storybook/types";
import {
  createMemoryHistory,
  createRootRoute,
  createRoute,
  createRouter,
  Outlet,
  RouterProvider,
} from "@tanstack/react-router";
import type { ReactNode } from "react";

const theme = createTheme({
  primaryColor: "blue",
  fontFamily: "Inter, system-ui, sans-serif",
  defaultRadius: "md",
});

function AppDecorator(Story: PartialStoryFn<ReactRenderer>): ReactNode {
  // Create a fresh router for each story to avoid state issues
  const rootRoute = createRootRoute({
    component: () => (
      <MantineProvider theme={theme}>
        <Notifications position="bottom-right" />
        <Outlet />
      </MantineProvider>
    ),
  });

  const indexRoute = createRoute({
    getParentRoute: () => rootRoute,
    path: "/",
    component: () => <Story />,
  });

  rootRoute.addChildren([indexRoute]);

  const router = createRouter({
    routeTree: rootRoute,
    history: createMemoryHistory({ initialEntries: ["/"] }),
  });

  return <RouterProvider router={router} />;
}

const preview: Preview = {
  decorators: [AppDecorator],
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
};

export default preview;
