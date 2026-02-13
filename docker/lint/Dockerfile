FROM php:8.5-cli-bookworm

# Install git, unzip, and bcmath extension
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install bcmath \
    && rm -rf /var/lib/apt/lists/*

# Git safe directory configuration
RUN git config --global --add safe.directory /app

# Add Node.js and npm for frontend linting
COPY --from=node:24-bookworm /usr/local/bin/node /usr/local/bin/node
COPY --from=node:24-bookworm /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# Add Composer for PHP dependency management
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
