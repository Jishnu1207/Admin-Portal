# Admin Portal

A comprehensive admin portal built with Symfony that provides robust customer and invoice management capabilities with advanced logging and monitoring features.

## Features

### Core Features
- User authentication and authorization
- Customer management (CRUD operations)
- Invoice management (CRUD operations)
- Advanced search and filtering capabilities
- Pagination support for all list views

### Logging & Monitoring
- Comprehensive activity logging
- Error tracking and monitoring
- Log filtering by type, date range, and search terms
- Detailed audit trails for all operations

### API Features
- Unified RESTful API endpoints for all entities
- JSON response format
- Pagination support
- Advanced filtering options
- Search functionality

## Requirements

- PHP 8.2 or higher
- MySQL 8.0 or higher
- Composer
- Symfony CLI (optional)
- Node.js and Yarn (for frontend assets)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd admin-portal
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install frontend dependencies:
```bash
yarn install
```

4. Configure your database in `.env`:
```
DATABASE_URL="mysql://root:@127.0.0.1:3306/admin_portal?serverVersion=8.0.32&charset=utf8mb4"
```

5. Create the database:
```bash
php bin/console doctrine:database:create
```

6. Run migrations:
```bash
php bin/console doctrine:migrations:migrate
```

7. Load initial data:
```bash
php bin/console doctrine:fixtures:load
```

8. Build frontend assets:
```bash
yarn build
```

## Running the Application

1. Start the Symfony development server:
```bash
symfony server:start
```

2. Visit `http://localhost:8000` in your browser

## API Documentation

### List Entities
- `GET /api/list/{entity}`
  - Query Parameters:
    - `page`: Page number (default: 1)
    - `limit`: Items per page (default: 10)
    - `search`: Search query
    - `type`: Filter by type
    - `startDate`: Filter by start date
    - `endDate`: Filter by end date

### Create Entity
- `POST /api/create/{entity}`
  - Content-Type: application/json

### Request Examples

#### Create Customer
```json
POST /api/create/customer
{
    "name": "John Doe",
    "phone": "1234567890",
    "email": "john@example.com",
    "address": "123 Main St"
}
```

#### Create Invoice
```json
POST /api/create/invoice
{
    "customer": "John Doe",
    "date": "2024-03-20",
    "amount": "1000",
    "status": 0
}
```

## Development

### Technology Stack
- Symfony 7.2
- Doctrine ORM
- MySQL 8.0
- Bootstrap 5
- Twig Templates
- PHPUnit for testing

### Running Tests
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Repository
./vendor/bin/phpunit tests/Service
```

### Code Style
- Follows PSR-12 coding standards
- Uses PHP CS Fixer for code formatting
- EditorConfig for consistent coding style

### Logging
The application uses a comprehensive logging system:
- Activity logs for user actions
- Error logs for system issues
- Detailed audit trails
- Log filtering and search capabilities

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details. 