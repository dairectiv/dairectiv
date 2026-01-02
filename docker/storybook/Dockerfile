FROM node:22-alpine

# Install pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

# Copy package files
COPY app/package.json app/pnpm-lock.yaml ./

# Install dependencies
RUN pnpm install --frozen-lockfile

# Copy source files
COPY app/ .

# Expose Storybook port
EXPOSE 6006

# Start Storybook development server (--no-open prevents browser launch attempt)
CMD ["pnpm", "storybook", "--host", "0.0.0.0", "--no-open"]
