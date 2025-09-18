# Tech Task Setup Guide

## Requirements

- PHP 8+ (PHP 8.3 recommended)
- Composer
- Node
- SQLite (default database)

## Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd tech-task
```

### 2. Install dependencies
```bash
composer install
npm install
```

### 3. Environment setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database setup
```bash
php artisan migrate
```

### 5. Start the development server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Features

### Caching
The application uses file-based caching, indicated by the `is_cached` field in JSON responses. To clear the cache:

```bash
php artisan cache:clear
```

### Rate Limiting
The `/lookup` endpoint has rate limiting middleware that allows 30 requests per IP address per minute. You can modify this in `app/Providers/RouteServiceProvider.php` if needed.

### Testing
Run the test suite:

```bash
php artisan test
```

For faster execution with parallel testing:

```bash
php artisan test --parallel
```

Note: Tests do not provide full code coverage.

## API Usage

The application provides a lookup endpoint that supports different gaming platforms:

### Available Endpoints

**Xbox Live Lookups:**
- Username: `http://localhost:8000/lookup?type=xbl&username=tebex`
- ID: `http://localhost:8000/lookup?type=xbl&id=2533274884045330`

**Steam Lookups:**
- ID only: `http://localhost:8000/lookup?type=steam&id=76561198806141009`
- Username: `http://localhost:8000/lookup?type=steam&username=test` (Returns error: "Steam only supports IDs")

**Minecraft Lookups:**
- Username: `http://localhost:8000/lookup?type=minecraft&username=Notch`
- ID: `http://localhost:8000/lookup?type=minecraft&id=d8d5a9237b2043d8883b1150148d6955`

**Error Testing:**
- Non-existent user: `http://localhost:8000/lookup?type=minecraft&username=NonExistentUser12345`

## Postman Collection

A Postman collection file (`postman_collections.json`) is included with pre-configured requests for all the above endpoints.

To import this collection into Postman:
1. Open Postman
2. Click "Import"
3. Select the `postman_collections.json` file

For detailed import instructions, visit: https://learning.postman.com/docs/getting-started/importing-and-exporting/importing-data/

## Project Structure

For detailed information about the tech test requirements and implementation details, see `INSTRUCTIONS.md`.
