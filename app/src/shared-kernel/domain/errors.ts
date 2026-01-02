/**
 * Base API error structure
 */
export interface ApiError {
  code: string;
  message: string;
  details?: Record<string, unknown>;
}

/**
 * Validation error with field-specific messages
 */
export interface ValidationError extends ApiError {
  code: "VALIDATION_ERROR";
  violations: Array<{
    field: string;
    message: string;
  }>;
}

/**
 * Type guard for validation errors
 */
export function isValidationError(error: unknown): error is ValidationError {
  return (
    typeof error === "object" &&
    error !== null &&
    "code" in error &&
    (error as ApiError).code === "VALIDATION_ERROR"
  );
}
