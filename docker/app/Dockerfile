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

# Expose port
EXPOSE 3000

# Start development server
CMD ["pnpm", "dev", "--host"]
