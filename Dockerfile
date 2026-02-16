# --- Stage 1: Base ---
# このステージを --target base でビルドして CI イメージとして利用する
FROM dunglas/frankenphp:1.11.1-php8.5-bookworm AS base

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions bcmath pcntl

# Add Node.js and npm for frontend
COPY --from=node:24-bookworm /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
RUN git config --global --add safe.directory /app

# --- Stage 2: Builder  ---
FROM base AS builder
COPY . .
RUN composer install --no-dev --optimize-autoloader
RUN npm ci && npm run build

# --- Stage 3: Production ---
FROM dunglas/frankenphp:1.11.1-php8.5-bookworm AS production

RUN install-php-extensions bcmath pcntl

# Disable HTTPS
ENV SERVER_NAME=:80

# Enable PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

WORKDIR /app

# Copy application from builder stage
COPY --from=builder /app /app
