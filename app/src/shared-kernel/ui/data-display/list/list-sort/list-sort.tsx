import { Select } from "@mantine/core";

export interface ListSortOption {
  /** Value in format "field:direction" (e.g., "createdAt:desc") */
  value: string;
  /** Display label */
  label: string;
}

export interface ListSortProps {
  /** Current sort value */
  value: string;
  /** Available sort options */
  options: ListSortOption[];
  /** Placeholder text */
  placeholder?: string;
  /** Callback when sort changes */
  onChange: (value: string) => void;
  /** Width of the dropdown */
  width?: number;
}

export function ListSort({
  value,
  options,
  placeholder = "Sort by",
  onChange,
  width = 180,
}: ListSortProps) {
  const handleChange = (newValue: string | null) => {
    if (newValue) {
      onChange(newValue);
    }
  };

  return (
    <Select
      placeholder={placeholder}
      data={options}
      value={value}
      onChange={handleChange}
      w={width}
    />
  );
}
