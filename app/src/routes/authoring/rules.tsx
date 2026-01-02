import { createFileRoute } from "@tanstack/react-router";
import { z } from "zod";
import { RulesListPage } from "@/authoring/feature/rules";

const searchSchema = z.object({
  page: z.number().min(1).optional().default(1),
});

export const Route = createFileRoute("/authoring/rules")({
  component: RulesListPage,
  validateSearch: searchSchema,
});
