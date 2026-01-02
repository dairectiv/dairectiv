import { createTheme, MantineProvider } from "@mantine/core";
import "@mantine/core/styles.css";
import { Notifications } from "@mantine/notifications";
import "@mantine/notifications/styles.css";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import { createRootRoute, createRoute, createRouter, Outlet } from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";

// Feature routes
import { rulesListRoute } from "@/authoring/rule/list";

// Home page (inline for now, can be moved to a feature later)
import { HomePage } from "@/home/pages/home.page";

// Theme configuration
const theme = createTheme({
  primaryColor: "blue",
  fontFamily: "Inter, system-ui, sans-serif",
  defaultRadius: "md",
});

// Root layout
function RootComponent() {
  return (
    <QueryClientProvider client={queryClient}>
      <MantineProvider theme={theme} defaultColorScheme="auto">
        <Notifications position="top-right" />
        <Outlet />
        <ReactQueryDevtools initialIsOpen={false} />
        <TanStackRouterDevtools position="bottom-right" />
      </MantineProvider>
    </QueryClientProvider>
  );
}

// Root route
export const rootRoute = createRootRoute({
  component: RootComponent,
});

// Index route
const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: "/",
  component: HomePage,
});

// Build route tree
const routeTree = rootRoute.addChildren([indexRoute, rulesListRoute]);

// Create and export router
export const router = createRouter({ routeTree });

// Type registration for type-safe navigation
declare module "@tanstack/react-router" {
  interface Register {
    router: typeof router;
  }
}
