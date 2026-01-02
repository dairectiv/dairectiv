import { Center, Group, Loader, Pagination, Stack, Text } from "@mantine/core";
import type { ComponentType, ReactNode } from "react";
import { ListEmpty } from "../list-empty";

export interface ListPagination {
  /** Current page number (1-indexed) */
  page: number;
  /** Total number of pages */
  totalPages: number;
  /** Total number of items */
  total: number;
}

export interface ListEmptyConfig {
  /** Icon component to display */
  icon?: ComponentType<{ size: number; color: string }>;
  /** Main title text */
  title: string;
  /** Optional subtitle or help text */
  subtitle?: string;
  /** Optional call-to-action element */
  action?: ReactNode;
}

export interface ListContainerProps {
  /** Items to render when not loading/error/empty */
  children: ReactNode;
  /** Whether data is currently loading */
  isLoading?: boolean;
  /** Whether an error occurred */
  isError?: boolean;
  /** Error message to display */
  errorMessage?: string;
  /** Error details/subtitle */
  errorDetails?: string;
  /** Whether the list is empty (no items) */
  isEmpty?: boolean;
  /** Empty state configuration */
  empty?: ListEmptyConfig;
  /** Pagination configuration */
  pagination?: ListPagination;
  /** Callback when page changes */
  onPageChange?: (page: number) => void;
  /** Custom loading element (defaults to Mantine Loader) */
  loadingElement?: ReactNode;
  /** Count of items shown, used for pagination text */
  itemCount?: number;
  /** Label for items (e.g., "rules", "commands") */
  itemLabel?: string;
}

export function ListContainer({
  children,
  isLoading = false,
  isError = false,
  errorMessage = "Failed to load data",
  errorDetails,
  isEmpty = false,
  empty,
  pagination,
  onPageChange,
  loadingElement,
  itemCount,
  itemLabel = "items",
}: ListContainerProps) {
  if (isLoading) {
    return <Center py="xl">{loadingElement ?? <Loader size="lg" />}</Center>;
  }

  if (isError) {
    return (
      <Center py="xl">
        <Stack align="center" gap="sm">
          <Text c="red" size="lg">
            {errorMessage}
          </Text>
          {errorDetails && (
            <Text c="dimmed" size="sm">
              {errorDetails}
            </Text>
          )}
        </Stack>
      </Center>
    );
  }

  if (isEmpty && empty) {
    return <ListEmpty {...empty} />;
  }

  const showPagination = pagination && pagination.totalPages > 1;

  return (
    <>
      <Stack gap="xs">{children}</Stack>
      {showPagination && (
        <Group justify="space-between" align="center" mt="md">
          <Text size="sm" c="dimmed">
            Showing {itemCount ?? 0} of {pagination.total} {itemLabel}
          </Text>
          <Pagination
            total={pagination.totalPages}
            value={pagination.page}
            onChange={onPageChange}
          />
        </Group>
      )}
    </>
  );
}
