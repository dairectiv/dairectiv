/**
 * UUID type alias for domain identifiers
 */
export type Uuid = string;

/**
 * ISO 8601 timestamp string
 */
export type Timestamp = string;

/**
 * Pagination parameters for list queries
 */
export interface PaginationParams {
  page: number;
  limit: number;
}

/**
 * Paginated response wrapper
 */
export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    total: number;
    page: number;
    limit: number;
    totalPages: number;
  };
}
