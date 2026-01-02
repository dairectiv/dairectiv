import { Badge, Group, type MantineColor, Paper, Stack, Text } from "@mantine/core";
import type { ReactNode } from "react";
import classes from "./list-card.module.css";

export interface ListCardProps {
  /** Main title of the card */
  title: string;
  /** Optional description displayed below the title */
  description?: string;
  /** Additional metadata displayed next to title (e.g., "3 days ago") */
  metadata?: string;
  /** Simple badge label (use with badgeColor) */
  badgeLabel?: string;
  /** Badge color (requires badgeLabel) */
  badgeColor?: MantineColor;
  /** Custom badge element (overrides badgeLabel/badgeColor) */
  badge?: ReactNode;
  /** Click handler - makes the card interactive */
  onClick?: () => void;
}

export function ListCard({
  title,
  description,
  metadata,
  badgeLabel,
  badgeColor = "gray",
  badge,
  onClick,
}: ListCardProps) {
  const badgeElement =
    badge ??
    (badgeLabel ? (
      <Badge color={badgeColor} variant="light" size="sm">
        {badgeLabel}
      </Badge>
    ) : null);
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
        {badgeElement}
      </Group>
    </Paper>
  );
}
