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

## Features

### Authentication & Profile Management

This application includes a complete authentication and profile management system built with Laravel Fortify:

#### User Features
- **ID/Password Authentication**: Users can register and login using email and password
- **Profile Management**: Users can update their name, display name, and email address
- **Display Name**: Required field (max 255 characters) shown in the UI
- **Email Verification**: Email verification flow with resend verification link
- **Password Reset**: Password reset via email link
- **Two-Factor Authentication**: Optional 2FA with QR code setup

#### Role-Based Access Control
- **User Roles**: Two roles - `admin` and `user` (default: `user`)
- **Role Middleware**: `role:admin` middleware for protecting admin-only routes
- **Helper Method**: `$user->isAdmin()` helper method on User model

#### User Management
- **Default Users**: Seeder creates two default users:
  - Admin: `admin@example.com` / `password` (role: admin)
  - User: `user@example.com` / `password` (role: user)

#### Database Schema
Users table includes:
- `id`: Primary key
- `name`: User's full name
- `display_name`: Name shown in UI (required, max 255 chars)
- `email`: Unique email address
- `role`: Enum ('admin', 'user') with default 'user'
- `password`: Hashed password
- `email_verified_at`: Email verification timestamp
- Two-factor authentication fields
- Timestamps

### Testing
All features are covered by comprehensive tests following TDD methodology:
- 54 passing tests with 157 assertions
- Unit and feature tests for authentication flows
- Profile management tests
- Role-based access control tests
- Display name validation tests

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

4. **Run database migrations and seed**
    ```bash
    php artisan migrate --seed
    ```
    This will create the database tables and seed default users.

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