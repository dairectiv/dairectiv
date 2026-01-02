import { Code, Group, ScrollArea, Text } from "@mantine/core";
import { type Icon, IconAdjustments, IconGauge, IconNotes } from "@tabler/icons-react";
import { LinksGroup, type LinksGroupLink } from "../links-group";
import { UserButton, type UserButtonProps } from "../user-button";
import classes from "./navbar.module.css";

export interface NavbarLink {
  label: string;
  icon: Icon;
  link?: string;
  initiallyOpened?: boolean;
  links?: LinksGroupLink[];
}

export interface NavbarProps {
  version?: string;
  links?: NavbarLink[];
  user?: UserButtonProps;
  onUserClick?: () => void;
}

const defaultLinks: NavbarLink[] = [
  { label: "Dashboard", icon: IconGauge, link: "/" },
  {
    label: "Authoring",
    icon: IconNotes,
    initiallyOpened: true,
    links: [
      { label: "Rules", link: "/authoring/rules" },
      { label: "Workflows", link: "/authoring/workflows" },
    ],
  },
  { label: "Settings", icon: IconAdjustments, link: "/settings" },
];

const defaultUser: UserButtonProps = {
  name: "Guest User",
  email: "guest@example.com",
};

export function Navbar({
  version = "0.0.0",
  links = defaultLinks,
  user = defaultUser,
  onUserClick,
}: NavbarProps) {
  const linksElements = links.map((item) => <LinksGroup {...item} key={item.label} />);

  return (
    <nav className={classes.navbar}>
      <div className={classes.header}>
        <Group justify="space-between">
          <Text fw={700} size="lg">
            dairectiv
          </Text>
          <Code fw={700}>v{version}</Code>
        </Group>
      </div>

      <ScrollArea className={classes.links}>
        <div className={classes.linksInner}>{linksElements}</div>
      </ScrollArea>

      <div className={classes.footer}>
        <UserButton {...user} onClick={onUserClick} />
      </div>
    </nav>
  );
}
