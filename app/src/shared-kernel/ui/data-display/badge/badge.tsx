import { Badge as MantineBadge, type MantineColor } from "@mantine/core";

export interface BadgeProps {
  /** The text label displayed in the badge */
  label: string;
  /** Badge color from Mantine color palette */
  color: MantineColor;
  /** Size of the badge */
  size?: "xs" | "sm" | "md" | "lg" | "xl";
}

export function Badge({ label, color, size = "sm" }: BadgeProps) {
  return (
    <MantineBadge color={color} variant="light" size={size}>
      {label}
    </MantineBadge>
  );
}
