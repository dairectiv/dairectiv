import { Center, Stack, Text } from "@mantine/core";
import { IconInbox } from "@tabler/icons-react";
import type { ComponentType, ReactNode } from "react";

export interface ListEmptyProps {
  /** Icon component to display (default: IconInbox) */
  icon?: ComponentType<{ size: number; color: string }>;
  /** Main title text */
  title: string;
  /** Optional subtitle or help text */
  subtitle?: string;
  /** Optional call-to-action element (button, link, etc.) */
  action?: ReactNode;
}

export function ListEmpty({ icon: Icon = IconInbox, title, subtitle, action }: ListEmptyProps) {
  return (
    <Center py="xl">
      <Stack align="center" gap="sm">
        <Icon size={48} color="var(--mantine-color-dimmed)" />
        <Text c="dimmed" size="lg">
          {title}
        </Text>
        {subtitle && (
          <Text c="dimmed" size="sm">
            {subtitle}
          </Text>
        )}
        {action}
      </Stack>
    </Center>
  );
}
