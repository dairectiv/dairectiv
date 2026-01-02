import {
  AppShell,
  Burger,
  Group,
  NavLink,
  Text,
  Title,
  useMantineColorScheme,
} from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import { IconHome, IconMoon, IconSun } from "@tabler/icons-react";
import { Link, Outlet } from "@tanstack/react-router";
import type { ReactNode } from "react";

interface AppLayoutProps {
  children?: ReactNode;
}

export function AppLayout({ children }: AppLayoutProps) {
  const [opened, { toggle }] = useDisclosure();
  const { colorScheme, toggleColorScheme } = useMantineColorScheme();

  return (
    <AppShell
      header={{ height: 60 }}
      navbar={{ width: 250, breakpoint: "sm", collapsed: { mobile: !opened } }}
      padding="md"
    >
      <AppShell.Header>
        <Group h="100%" px="md" justify="space-between">
          <Group>
            <Burger opened={opened} onClick={toggle} hiddenFrom="sm" size="sm" />
            <Title order={3}>dairectiv</Title>
          </Group>
          <Group>
            <Text
              component="button"
              onClick={toggleColorScheme}
              style={{ cursor: "pointer", background: "none", border: "none" }}
            >
              {colorScheme === "dark" ? <IconSun size={20} /> : <IconMoon size={20} />}
            </Text>
          </Group>
        </Group>
      </AppShell.Header>

      <AppShell.Navbar p="md">
        <NavLink component={Link} to="/" label="Home" leftSection={<IconHome size={16} />} />
      </AppShell.Navbar>

      <AppShell.Main>{children || <Outlet />}</AppShell.Main>
    </AppShell>
  );
}
