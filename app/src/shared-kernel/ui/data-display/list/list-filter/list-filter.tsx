import { Select } from "@mantine/core";

export interface ListFilterOption {
  /** Value used in the callback */
  value: string;
  /** Display label */
  label: string;
}

export interface ListFilterProps {
  /** Current filter value */
  value?: string;
  /** Available filter options */
  options: ListFilterOption[];
  /** Placeholder text */
  placeholder?: string;
  /** Callback when filter changes, undefined when cleared */
  onChange: (value: string | undefined) => void;
  /** Allow clearing the filter */
  clearable?: boolean;
  /** Width of the dropdown */
  width?: number;
}

export function ListFilter({
  value,
  options,
  placeholder = "Filter",
  onChange,
  clearable = true,
  width = 150,
}: ListFilterProps) {
  const handleChange = (newValue: string | null) => {
    onChange(newValue || undefined);
  };

  return (
    <Select
      placeholder={placeholder}
      data={options}
      value={value ?? ""}
      onChange={handleChange}
      clearable={clearable}
      w={width}
    />
  );
}
