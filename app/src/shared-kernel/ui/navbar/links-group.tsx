import { Box, Collapse, Group, ThemeIcon, UnstyledButton } from "@mantine/core";
import { type Icon, IconChevronRight } from "@tabler/icons-react";
import { Link, useRouterState } from "@tanstack/react-router";
import { useState } from "react";
import classes from "./links-group.module.css";

export interface LinksGroupLink {
  label: string;
  link: string;
}

export interface LinksGroupProps {
  icon: Icon;
  label: string;
  initiallyOpened?: boolean;
  links?: LinksGroupLink[];
  link?: string;
}

export function LinksGroup({ icon: Icon, label, initiallyOpened, links, link }: LinksGroupProps) {
  const hasLinks = Array.isArray(links) && links.length > 0;
  const [opened, setOpened] = useState(initiallyOpened ?? false);
  const routerState = useRouterState();
  const currentPath = routerState.location.pathname;

  const items = hasLinks
    ? links.map((item) => (
        <Link
          className={classes.link}
          data-active={currentPath === item.link || undefined}
          to={item.link}
          key={item.label}
        >
          {item.label}
        </Link>
      ))
    : null;

  // Simple link without nested items
  if (!hasLinks && link) {
    return (
      <Link to={link} className={classes.control} data-active={currentPath === link || undefined}>
        <Group gap={0} justify="space-between">
          <Box style={{ display: "flex", alignItems: "center" }}>
            <ThemeIcon variant="light" size={30}>
              <Icon size={18} />
            </ThemeIcon>
            <Box ml="md">{label}</Box>
          </Box>
        </Group>
      </Link>
    );
  }

  return (
    <>
      <UnstyledButton onClick={() => setOpened((o) => !o)} className={classes.control}>
        <Group gap={0} justify="space-between">
          <Box style={{ display: "flex", alignItems: "center" }}>
            <ThemeIcon variant="light" size={30}>
              <Icon size={18} />
            </ThemeIcon>
            <Box ml="md">{label}</Box>
          </Box>
          {hasLinks && (
            <IconChevronRight
              className={classes.chevron}
              stroke={1.5}
              size={16}
              data-rotate={opened || undefined}
            />
          )}
        </Group>
      </UnstyledButton>
      {hasLinks && <Collapse in={opened}>{items}</Collapse>}
    </>
  );
}
