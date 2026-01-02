import { Card, Container, SimpleGrid, Text, ThemeIcon, Title } from "@mantine/core";
import { AppLayout } from "@shared/ui/layout";
import { IconBrain, IconRocket, IconTools, IconUsers } from "@tabler/icons-react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/")({
  component: HomePage,
});

const features = [
  {
    icon: IconBrain,
    title: "AI Governance",
    description:
      "Author, version, and govern AI directives from a single source of truth for your engineering teams.",
  },
  {
    icon: IconTools,
    title: "Multi-Tool Support",
    description:
      "Sync guidance to native formats: AGENTS.md, Cursor rules, Claude Code, JetBrains AI, and more.",
  },
  {
    icon: IconUsers,
    title: "Team Collaboration",
    description:
      "Collaborate on rules, skills, playbooks, and subagents with your entire engineering organization.",
  },
  {
    icon: IconRocket,
    title: "Seamless Integration",
    description:
      "Integrate with your existing workflow through CLI tools, CI/CD pipelines, and IDE extensions.",
  },
];

function HomePage() {
  return (
    <AppLayout>
      <Container size="lg" py="xl">
        <Title order={1} ta="center" mb="md">
          Welcome to dairectiv
        </Title>
        <Text c="dimmed" ta="center" mb="xl" maw={600} mx="auto">
          The AI enablement hub for engineering teams. Create, manage, and sync AI guidance across
          all your development tools.
        </Text>

        <SimpleGrid cols={{ base: 1, sm: 2 }} spacing="lg">
          {features.map((feature) => (
            <Card key={feature.title} shadow="sm" padding="lg" radius="md" withBorder>
              <ThemeIcon size="xl" radius="md" mb="md">
                <feature.icon size={24} />
              </ThemeIcon>
              <Text fw={500} size="lg" mb="xs">
                {feature.title}
              </Text>
              <Text size="sm" c="dimmed">
                {feature.description}
              </Text>
            </Card>
          ))}
        </SimpleGrid>
      </Container>
    </AppLayout>
  );
}
