import {
  Alert,
  Badge,
  Button,
  Card,
  Center,
  Group,
  Loader,
  Stack,
  Text,
  Timeline,
  Title,
} from "@mantine/core";
import type {
  DirectiveState,
  StepResponse,
  WorkflowExampleResponse,
  WorkflowResponse,
} from "@shared/infrastructure/api/generated/types.gen";
import { IconAlertCircle, IconEdit, IconInfoCircle } from "@tabler/icons-react";

const stateBadgeConfig: Record<DirectiveState, { label: string; color: string }> = {
  draft: { label: "Draft", color: "yellow" },
  published: { label: "Published", color: "green" },
  archived: { label: "Archived", color: "gray" },
  deleted: { label: "Deleted", color: "red" },
};

interface WorkflowStepsProps {
  steps: StepResponse[];
}

function WorkflowSteps({ steps }: WorkflowStepsProps) {
  if (steps.length === 0) {
    return (
      <Alert icon={<IconInfoCircle size={16} />} color="gray">
        No steps defined yet. Add steps to guide the workflow execution.
      </Alert>
    );
  }

  const sortedSteps = [...steps].sort((a, b) => a.order - b.order);

  return (
    <Timeline active={sortedSteps.length} bulletSize={24} lineWidth={2}>
      {sortedSteps.map((step, index) => (
        <Timeline.Item key={step.id} title={`Step ${index + 1}`}>
          <Text c="dimmed" size="sm">
            {step.content}
          </Text>
        </Timeline.Item>
      ))}
    </Timeline>
  );
}

interface WorkflowExamplesProps {
  examples: WorkflowExampleResponse[];
}

function WorkflowExamples({ examples }: WorkflowExamplesProps) {
  if (examples.length === 0) {
    return (
      <Alert icon={<IconInfoCircle size={16} />} color="gray">
        No examples defined yet. Add examples to demonstrate the workflow in action.
      </Alert>
    );
  }

  return (
    <Stack gap="md">
      {examples.map((example) => (
        <Card key={example.id} withBorder p="md">
          <Stack gap="xs">
            <Text fw={500}>{example.scenario}</Text>
            <Group gap="xs">
              <Badge size="sm" color="blue" variant="light">
                Input
              </Badge>
              <Text size="sm" c="dimmed">
                {example.input}
              </Text>
            </Group>
            <Group gap="xs">
              <Badge size="sm" color="green" variant="light">
                Output
              </Badge>
              <Text size="sm" c="dimmed">
                {example.output}
              </Text>
            </Group>
            {example.explanation && (
              <Text size="xs" c="dimmed" fs="italic">
                {example.explanation}
              </Text>
            )}
          </Stack>
        </Card>
      ))}
    </Stack>
  );
}

export interface WorkflowDetailProps {
  workflow?: WorkflowResponse;
  isLoading: boolean;
  isError: boolean;
  error?: Error | null;
}

export function WorkflowDetail({ workflow, isLoading, isError, error }: WorkflowDetailProps) {
  if (isLoading) {
    return (
      <Center py="xl">
        <Loader size="lg" />
      </Center>
    );
  }

  if (isError) {
    return (
      <Alert icon={<IconAlertCircle size={16} />} title="Error loading workflow" color="red">
        {error?.message ?? "An unexpected error occurred"}
      </Alert>
    );
  }

  if (!workflow) {
    return (
      <Alert icon={<IconAlertCircle size={16} />} title="Workflow not found" color="yellow">
        The requested workflow could not be found.
      </Alert>
    );
  }

  const badgeConfig = stateBadgeConfig[workflow.state];

  return (
    <Stack gap="lg">
      <Group justify="space-between" align="flex-start">
        <Stack gap="xs">
          <Group gap="sm">
            <Title order={2}>{workflow.name}</Title>
            <Badge color={badgeConfig.color}>{badgeConfig.label}</Badge>
          </Group>
          <Text c="dimmed">{workflow.description}</Text>
        </Stack>
        {workflow.state === "draft" && (
          <Button
            component="a"
            href={`/authoring/workflows/${workflow.id}/edit`}
            leftSection={<IconEdit size={16} />}
            variant="light"
          >
            Edit
          </Button>
        )}
      </Group>

      {workflow.content && (
        <Card withBorder p="lg">
          <Stack gap="sm">
            <Title order={4}>Content</Title>
            <Text style={{ whiteSpace: "pre-wrap" }}>{workflow.content}</Text>
          </Stack>
        </Card>
      )}

      <Card withBorder p="lg">
        <Stack gap="sm">
          <Title order={4}>Steps ({workflow.steps.length})</Title>
          <WorkflowSteps steps={workflow.steps} />
        </Stack>
      </Card>

      <Card withBorder p="lg">
        <Stack gap="sm">
          <Title order={4}>Examples ({workflow.examples.length})</Title>
          <WorkflowExamples examples={workflow.examples} />
        </Stack>
      </Card>
    </Stack>
  );
}
