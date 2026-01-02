import { Group, Paper, Stack, Text } from "@mantine/core";
import type { ReactNode } from "react";
import classes from "./list-card.module.css";

export interface ListCardProps {
  title: string;
  description?: string;
  metadata?: string;
  badge?: ReactNode;
  onClick?: () => void;
}

export function ListCard({ title, description, metadata, badge, onClick }: ListCardProps) {
  return (
    <Paper
      className={classes.item}
      p="md"
      withBorder
      onClick={onClick}
      role={onClick ? "button" : undefined}
      tabIndex={onClick ? 0 : undefined}
      onKeyDown={
        onClick
          ? (e) => {
              if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                onClick();
              }
            }
          : undefined
      }
    >
      <Group justify="space-between" wrap="nowrap" align="flex-start">
        <Stack gap={4} style={{ flex: 1, minWidth: 0 }}>
          <Group gap="xs" align="center">
            <Text fw={500} size="sm" className={classes.title}>
              {title}
            </Text>
            {metadata && (
              <Text c="dimmed" size="xs">
                · {metadata}
              </Text>
            )}
          </Group>
          {description && (
            <Text c="dimmed" size="xs" lineClamp={2} className={classes.description}>
              {description}
            </Text>
          )}
        </Stack>
        {badge}
      </Group>
    </Paper>
  );
}
