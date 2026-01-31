# BookVault-Laravel

## Tech Stack

- **Backend**: Laravel 12 with PHP 8.5
- **Frontend**: Inertia.js v2 with TypeScript
- **Authentication**: Laravel Fortify
- **Testing**: Pest v4
- **Database**: PostgreSQL 18
- **Cache/Queue**: Redis
- **Search**: Meilisearch
- **Code Formatting**: Laravel Pint
- **Routing**: Laravel Wayfinder
- **Development Environment**: Dev Container

## Getting Started

This project uses Dev Containers for development.

### Prerequisites

- Docker Desktop
- Visual Studio Code
- Dev Containers extension

### Setup Instructions

1. **Clone the repository**
    ```bash
    git clone <repository-url>
    cd BookVault-Laravel
    ```

2. **Open in Dev Container**
    - Open the project in VS Code
    - Open the Command Palette (F1 or Ctrl+Shift+P)
    - Select "Dev Containers: Reopen in Container"
    - Wait for the container to build and start

3. **Set up environment**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Run database migrations**
    ```bash
    php artisan migrate
    ```

5. **Start development server**
    ```bash
    composer run dev
    ```
    or
    ```bash
    npm run dev
    ```

6. **Run tests**
    ```bash
    php artisan test --compact
    ```

### Access

- **Application**: http://localhost
- **Vite Dev Server**: http://localhost:5173
- **Meilisearch**: http://localhost:7700

### Available Services

- PostgreSQL (Port: 5432)
- Redis (Port: 6379)
- Meilisearch (Port: 7700)