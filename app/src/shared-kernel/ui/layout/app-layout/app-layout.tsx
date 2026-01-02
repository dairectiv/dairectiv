import { AppShell, Burger, Group, useMantineColorScheme } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import { IconMoon, IconSun } from "@tabler/icons-react";
import { Outlet } from "@tanstack/react-router";
import type { ReactNode } from "react";
import { Navbar, type NavbarProps } from "../../navigation/navbar";
import classes from "./app-layout.module.css";

export interface AppLayoutProps {
  children?: ReactNode;
  navbarProps?: Omit<NavbarProps, "onUserClick">;
  onUserClick?: () => void;
}

export function AppLayout({ children, navbarProps, onUserClick }: AppLayoutProps) {
  const [opened, { toggle }] = useDisclosure();
  const { colorScheme, toggleColorScheme } = useMantineColorScheme();

  return (
    <AppShell navbar={{ width: 300, breakpoint: "sm", collapsed: { mobile: !opened } }} padding={0}>
      <AppShell.Navbar p={0}>
        <div className={classes.mobileHeader}>
          <Group justify="space-between" h="100%" px="md">
            <Burger opened={opened} onClick={toggle} size="sm" />
            <button
              type="button"
              onClick={toggleColorScheme}
              className={classes.colorSchemeToggle}
              aria-label="Toggle color scheme"
            >
              {colorScheme === "dark" ? <IconSun size={20} /> : <IconMoon size={20} />}
            </button>
          </Group>
        </div>
        <Navbar {...navbarProps} onUserClick={onUserClick} />
      </AppShell.Navbar>

      <AppShell.Main className={classes.main}>
        <div className={classes.content}>{children || <Outlet />}</div>
      </AppShell.Main>
    </AppShell>
  );
}
