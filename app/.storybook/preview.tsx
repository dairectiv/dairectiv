import { createTheme, MantineProvider } from "@mantine/core";
import "@mantine/core/styles.css";
import "@mantine/notifications/styles.css";
import type { Preview, ReactRenderer } from "@storybook/react";
import type { PartialStoryFn } from "@storybook/types";
import type { ReactNode } from "react";

const theme = createTheme({
  primaryColor: "blue",
  fontFamily: "Inter, system-ui, sans-serif",
  defaultRadius: "md",
});

function MantineDecorator(Story: PartialStoryFn<ReactRenderer>): ReactNode {
  return (
    <MantineProvider theme={theme}>
      <Story />
    </MantineProvider>
  );
}

const preview: Preview = {
  decorators: [MantineDecorator],
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
