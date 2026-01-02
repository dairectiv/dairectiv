import { TextInput } from "@mantine/core";
import { useDebouncedCallback } from "@mantine/hooks";
import { IconSearch } from "@tabler/icons-react";

export interface ListSearchProps {
  /** Current search value */
  value?: string;
  /** Placeholder text */
  placeholder?: string;
  /** Callback when search value changes (debounced) */
  onChange: (value: string) => void;
  /** Debounce delay in milliseconds */
  debounceMs?: number;
  /** Maximum width of the input */
  maxWidth?: number;
}

export function ListSearch({
  value,
  placeholder = "Search...",
  onChange,
  debounceMs = 300,
  maxWidth = 300,
}: ListSearchProps) {
  const debouncedSearch = useDebouncedCallback((searchValue: string) => {
    onChange(searchValue);
  }, debounceMs);

  return (
    <TextInput
      placeholder={placeholder}
      leftSection={<IconSearch size={16} />}
      defaultValue={value}
      onChange={(e) => debouncedSearch(e.currentTarget.value)}
      style={{ flex: 1, maxWidth }}
    />
  );
}
