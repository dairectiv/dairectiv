import { Group, Select, TextInput } from "@mantine/core";
import { useDebouncedCallback } from "@mantine/hooks";
import { IconSearch } from "@tabler/icons-react";
import type { RulesListStateFilter } from "../hooks/use-rules-list";

export interface RulesListToolbarProps {
  search?: string;
  state?: RulesListStateFilter;
  sortBy?: "name" | "createdAt" | "updatedAt";
  sortOrder?: "asc" | "desc";
  onSearchChange: (search: string) => void;
  onStateChange: (state: RulesListStateFilter | undefined) => void;
  onSortChange: (sortBy: "name" | "createdAt" | "updatedAt", sortOrder: "asc" | "desc") => void;
}

const stateOptions = [
  { value: "", label: "All states" },
  { value: "draft", label: "Draft" },
  { value: "published", label: "Published" },
  { value: "archived", label: "Archived" },
];

const sortOptions = [
  { value: "createdAt:desc", label: "Newest first" },
  { value: "createdAt:asc", label: "Oldest first" },
  { value: "updatedAt:desc", label: "Recently updated" },
  { value: "name:asc", label: "Name A-Z" },
  { value: "name:desc", label: "Name Z-A" },
];

export function RulesListToolbar({
  search,
  state,
  sortBy = "createdAt",
  sortOrder = "desc",
  onSearchChange,
  onStateChange,
  onSortChange,
}: RulesListToolbarProps) {
  const debouncedSearch = useDebouncedCallback((value: string) => {
    onSearchChange(value);
  }, 300);

  const handleSortChange = (value: string | null) => {
    if (!value) return;
    const [newSortBy, newSortOrder] = value.split(":") as [
      "name" | "createdAt" | "updatedAt",
      "asc" | "desc",
    ];
    onSortChange(newSortBy, newSortOrder);
  };

  return (
    <Group gap="sm">
      <TextInput
        placeholder="Search rules..."
        leftSection={<IconSearch size={16} />}
        defaultValue={search}
        onChange={(e) => debouncedSearch(e.currentTarget.value)}
        style={{ flex: 1, maxWidth: 300 }}
      />
      <Select
        placeholder="Filter by state"
        data={stateOptions}
        value={state ?? ""}
        onChange={(value) => onStateChange(value ? (value as RulesListStateFilter) : undefined)}
        clearable
        w={150}
      />
      <Select
        placeholder="Sort by"
        data={sortOptions}
        value={`${sortBy}:${sortOrder}`}
        onChange={handleSortChange}
        w={180}
      />
    </Group>
  );
}
