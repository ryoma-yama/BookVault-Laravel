FROM dunglas/frankenphp:1.11.1-php8.5-bookworm AS builder

# for composer installation
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node:24-bookworm /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN install-php-extensions bcmath

WORKDIR /app
COPY . .

# 2. Wayfinder実行のために必要なものを揃えてビルド
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev
RUN npm install
RUN npm run build

# Stage 2: Backend setup with FrankenPHP
FROM dunglas/frankenphp:1.11.1-php8.5-bookworm

# Install needed PHP extensions for this project
RUN install-php-extensions bcmath

# Be sure to replace "your-domain-name.example.com" by your domain name
ENV SERVER_NAME=your-domain-name.example.com
# If you want to disable HTTPS, use this value instead:
ENV SERVER_NAME=:80

# If your project is not using the "public" directory as the web root, you can set it here:
# ENV SERVER_ROOT=web/

# Enable PHP production settings
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=builder /app /app