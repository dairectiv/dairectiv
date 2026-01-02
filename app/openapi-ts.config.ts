import { defineConfig } from "@hey-api/openapi-ts";

export default defineConfig({
  input: "../oas/openapi.yaml",
  output: {
    path: "./src/shared-kernel/infrastructure/api/generated",
    format: "biome",
  },
  plugins: [
    "@hey-api/schemas",
    "@hey-api/sdk",
    {
      name: "@hey-api/transformers",
      dates: true,
    },
    {
      name: "@hey-api/client-axios",
    },
    {
      name: "@tanstack/react-query",
    },
  ],
});
