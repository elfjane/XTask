FROM node:24.12-alpine AS base

WORKDIR /app

# --- Development Stage ---
FROM base AS development
COPY package*.json ./
RUN npm install
COPY . .
EXPOSE 3111
CMD ["npm", "run", "dev"]

# --- Production Builder Stage ---
FROM base AS builder
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Production Stage ---
FROM base AS production
COPY --from=builder /app/.output ./.output
EXPOSE 3111
CMD ["node", ".output/server/index.mjs"]
