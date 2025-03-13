# Inventory Management System

This Inventory Management System is built using Laravel for the backend and Vue 3 with Inertia.js, PrimeVue, and Tailwind CSS for the frontend. It supports multi-warehouse stock management, dynamic product variants, stock transactions, and stock transfers.

## Features

- Multi-warehouse stock management
- Dynamic product variants with automatic SKU generation
- Stock-in, stock-out, and stock transfer tracking
- Role-based access control (Spatie Permission)
- RESTful API support
- Scalable and maintainable using SOLID principles
- Automated tests using PestPHP

## Prerequisites

Ensure you have the following installed:

- PHP 8.1 or higher
- Composer
- Node.js (v16 or higher) & npm
- MySQL
- Git

## Installation

### Clone the repository

```sh
git clone https://github.com/ljsharp/inventory_manager.git
cd inventory_manager
```

### Backend Setup

1. Install dependencies:

```sh
composer install
```

2. Copy the environment file and configure the database:

```sh
cp .env.example .env
```

3. Generate the application key:

```sh
php artisan key:generate
```

4. Set up database and run migrations with seeders:

```sh
php artisan migrate --seed
```

5. (Optional) Run the queue worker:

```sh
php artisan queue:work
```

6. Start the development server:

```sh
php artisan serve
```

### Frontend Setup

1. Navigate to the frontend directory:

```sh
cd frontend
```

2. Install dependencies:

```sh
npm install
```

3. Compile assets and start development server:

```sh
npm run dev
```

### Running Tests

Run Pest tests for the backend:

```sh
php artisan test
```

### Seeder Information

The seeders create default categories, products, stocks, stock transfer and stock transactions warehouses, and test users.

## License

This project is licensed under the MIT License.
