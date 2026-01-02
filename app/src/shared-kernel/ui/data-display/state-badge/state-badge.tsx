import { Badge, type MantineColor } from "@mantine/core";
import type { DirectiveState } from "@shared/infrastructure/api/generated/types.gen";

const stateConfig: Record<DirectiveState, { color: MantineColor; label: string }> = {
  draft: { color: "yellow", label: "Draft" },
  published: { color: "green", label: "Published" },
  archived: { color: "gray", label: "Archived" },
  deleted: { color: "red", label: "Deleted" },
};

export interface StateBadgeProps {
  state: DirectiveState;
  size?: "xs" | "sm" | "md" | "lg" | "xl";
}

export function StateBadge({ state, size = "sm" }: StateBadgeProps) {
  const { color, label } = stateConfig[state];

  return (
    <Badge color={color} variant="light" size={size}>
      {label}
    </Badge>
  );
}
