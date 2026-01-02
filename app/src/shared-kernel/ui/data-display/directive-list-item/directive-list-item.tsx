import { Group, Paper, Stack, Text } from "@mantine/core";
import type { DirectiveState } from "@shared/infrastructure/api/generated/types.gen";
import { StateBadge } from "../state-badge";
import classes from "./directive-list-item.module.css";

export interface DirectiveListItemProps {
  name: string;
  description: string;
  state: DirectiveState;
  onClick?: () => void;
}

export function DirectiveListItem({ name, description, state, onClick }: DirectiveListItemProps) {
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
          <Text fw={500} size="sm" className={classes.name}>
            {name}
          </Text>
          <Text c="dimmed" size="xs" lineClamp={2} className={classes.description}>
            {description}
          </Text>
        </Stack>
        <StateBadge state={state} />
      </Group>
    </Paper>
  );
}
