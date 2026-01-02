import { Avatar, Group, Text, UnstyledButton } from "@mantine/core";
import { IconChevronRight } from "@tabler/icons-react";
import classes from "./user-button.module.css";

export interface UserButtonProps {
  image?: string;
  name: string;
  email: string;
  onClick?: () => void;
}

export function UserButton({ image, name, email, onClick }: UserButtonProps) {
  return (
    <UnstyledButton className={classes.user} onClick={onClick}>
      <Group>
        <Avatar src={image} radius="xl" />

        <div style={{ flex: 1 }}>
          <Text size="sm" fw={500}>
            {name}
          </Text>

          <Text c="dimmed" size="xs">
            {email}
          </Text>
        </div>

        <IconChevronRight size={14} stroke={1.5} />
      </Group>
    </UnstyledButton>
  );
}
