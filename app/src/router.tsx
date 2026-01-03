import { createTheme, MantineProvider } from "@mantine/core";
import "@mantine/core/styles.css";
import "@mantine/notifications/styles.css";
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { NotificationContainer } from "@shared/ui/feedback/notification";
import { QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import { createRootRoute, createRoute, createRouter, Outlet } from "@tanstack/react-router";
import { TanStackRouterDevtools } from "@tanstack/react-router-devtools";

// Feature routes
import { createRuleRoute } from "@/authoring/rule/create";
import { ruleDetailRoute } from "@/authoring/rule/detail";
import { editRuleRoute } from "@/authoring/rule/edit";
import { rulesListRoute } from "@/authoring/rule/list";
import { createWorkflowRoute } from "@/authoring/workflow/create";
import { workflowDetailRoute } from "@/authoring/workflow/detail";
import { editWorkflowRoute } from "@/authoring/workflow/edit";
import { workflowsListRoute } from "@/authoring/workflow/list";

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
        <NotificationContainer />
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
const routeTree = rootRoute.addChildren([
  indexRoute,
  rulesListRoute,
  ruleDetailRoute,
  createRuleRoute,
  editRuleRoute,
  workflowsListRoute,
  workflowDetailRoute,
  createWorkflowRoute,
  editWorkflowRoute,
]);

// Create and export router
export const router = createRouter({ routeTree });

// Type registration for type-safe navigation
declare module "@tanstack/react-router" {
  interface Register {
    router: typeof router;
  }
}
