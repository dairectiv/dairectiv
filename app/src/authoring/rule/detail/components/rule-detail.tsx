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
  Title,
} from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import type {
  DirectiveState,
  RuleExampleResponse,
  RuleResponse,
} from "@shared/infrastructure/api/generated/types.gen";
import { ConfirmModal } from "@shared/ui/feedback";
import {
  IconAlertCircle,
  IconArchive,
  IconCheck,
  IconEdit,
  IconInfoCircle,
  IconSend,
  IconTrash,
  IconX,
} from "@tabler/icons-react";

const stateBadgeConfig: Record<DirectiveState, { label: string; color: string }> = {
  draft: { label: "Draft", color: "yellow" },
  published: { label: "Published", color: "green" },
  archived: { label: "Archived", color: "gray" },
  deleted: { label: "Deleted", color: "red" },
};

interface RuleExamplesProps {
  examples: RuleExampleResponse[];
}

function RuleExamples({ examples }: RuleExamplesProps) {
  if (examples.length === 0) {
    return (
      <Alert icon={<IconInfoCircle size={16} />} color="gray">
        No examples defined yet. Add examples to demonstrate the rule in action.
      </Alert>
    );
  }

  return (
    <Stack gap="md">
      {examples.map((example) => (
        <Card key={example.id} withBorder p="md">
          <Stack gap="sm">
            {example.good && (
              <Group gap="xs" align="flex-start">
                <Badge
                  size="sm"
                  color="green"
                  variant="light"
                  leftSection={<IconCheck size={12} />}
                >
                  Good
                </Badge>
                <Text size="sm" style={{ flex: 1, whiteSpace: "pre-wrap" }}>
                  {example.good}
                </Text>
              </Group>
            )}
            {example.bad && (
              <Group gap="xs" align="flex-start">
                <Badge size="sm" color="red" variant="light" leftSection={<IconX size={12} />}>
                  Bad
                </Badge>
                <Text size="sm" style={{ flex: 1, whiteSpace: "pre-wrap" }}>
                  {example.bad}
                </Text>
              </Group>
            )}
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

export interface RuleDetailProps {
  rule?: RuleResponse;
  isLoading: boolean;
  isError: boolean;
  error?: Error | null;
  onPublish?: () => void;
  isPublishing?: boolean;
  onArchive?: () => void;
  isArchiving?: boolean;
  onDelete?: () => void;
  isDeleting?: boolean;
}

export function RuleDetail({
  rule,
  isLoading,
  isError,
  error,
  onPublish,
  isPublishing = false,
  onArchive,
  isArchiving = false,
  onDelete,
  isDeleting = false,
}: RuleDetailProps) {
  const [publishModalOpened, { open: openPublishModal, close: closePublishModal }] =
    useDisclosure(false);
  const [archiveModalOpened, { open: openArchiveModal, close: closeArchiveModal }] =
    useDisclosure(false);
  const [deleteModalOpened, { open: openDeleteModal, close: closeDeleteModal }] =
    useDisclosure(false);

  const handleConfirmPublish = () => {
    onPublish?.();
    closePublishModal();
  };

  const handleConfirmArchive = () => {
    onArchive?.();
    closeArchiveModal();
  };

  const handleConfirmDelete = () => {
    onDelete?.();
    closeDeleteModal();
  };

  if (isLoading) {
    return (
      <Center py="xl">
        <Loader size="lg" />
      </Center>
    );
  }

  if (isError) {
    return (
      <Alert icon={<IconAlertCircle size={16} />} title="Error loading rule" color="red">
        {error?.message ?? "An unexpected error occurred"}
      </Alert>
    );
  }

  if (!rule) {
    return (
      <Alert icon={<IconAlertCircle size={16} />} title="Rule not found" color="yellow">
        The requested rule could not be found.
      </Alert>
    );
  }

  const badgeConfig = stateBadgeConfig[rule.state];
  const canArchive = rule.state === "draft" || rule.state === "published";
  const canDelete = rule.state !== "deleted";

  return (
    <>
      <Stack gap="lg">
        <Group justify="space-between" align="flex-start">
          <Stack gap="xs">
            <Group gap="sm">
              <Title order={2}>{rule.name}</Title>
              <Badge color={badgeConfig.color}>{badgeConfig.label}</Badge>
            </Group>
            <Text c="dimmed">{rule.description}</Text>
          </Stack>
          <Group gap="xs">
            {rule.state === "draft" && onPublish && (
              <Button
                leftSection={<IconSend size={16} />}
                variant="light"
                color="teal"
                onClick={openPublishModal}
                loading={isPublishing}
              >
                Publish
              </Button>
            )}
            {rule.state === "draft" && (
              <Button
                component="a"
                href={`/authoring/rules/${rule.id}/edit`}
                leftSection={<IconEdit size={16} />}
                variant="light"
              >
                Edit
              </Button>
            )}
            {canArchive && onArchive && (
              <Button
                leftSection={<IconArchive size={16} />}
                variant="light"
                color="orange"
                onClick={openArchiveModal}
                loading={isArchiving}
              >
                Archive
              </Button>
            )}
            {canDelete && onDelete && (
              <Button
                leftSection={<IconTrash size={16} />}
                variant="light"
                color="red"
                onClick={openDeleteModal}
                loading={isDeleting}
              >
                Delete
              </Button>
            )}
          </Group>
        </Group>

        {rule.content && (
          <Card withBorder p="lg">
            <Stack gap="sm">
              <Title order={4}>Content</Title>
              <Text style={{ whiteSpace: "pre-wrap" }}>{rule.content}</Text>
            </Stack>
          </Card>
        )}

        <Card withBorder p="lg">
          <Stack gap="sm">
            <Title order={4}>Examples ({rule.examples.length})</Title>
            <RuleExamples examples={rule.examples} />
          </Stack>
        </Card>
      </Stack>

      <ConfirmModal
        opened={publishModalOpened}
        onClose={closePublishModal}
        onConfirm={handleConfirmPublish}
        title="Publish Rule"
        message="Publishing this rule will make it available to AI tools. Are you sure you want to proceed?"
        confirmLabel="Publish"
        confirmColor="teal"
        isLoading={isPublishing}
      />

      <ConfirmModal
        opened={archiveModalOpened}
        onClose={closeArchiveModal}
        onConfirm={handleConfirmArchive}
        title="Archive Rule"
        message="Are you sure you want to archive this rule? It will no longer be visible to AI tools and cannot be edited. You can restore it later from the archived items."
        confirmLabel="Archive"
        confirmColor="orange"
        isLoading={isArchiving}
      />

      <ConfirmModal
        opened={deleteModalOpened}
        onClose={closeDeleteModal}
        onConfirm={handleConfirmDelete}
        title="Delete Rule"
        message={`Are you sure you want to delete "${rule.name}"? This action cannot be undone. All data associated with this rule will be permanently deleted.`}
        confirmLabel="Delete"
        confirmColor="red"
        isLoading={isDeleting}
      />
    </>
  );
}
