# Inventory Management System

This Inventory Management System (IMS) is built using **Laravel (Backend)** with **Vue 3, Inertia.js, PrimeVue, and Tailwind CSS (Frontend)**. It supports **multi-warehouse stock management, dynamic product variants, stock transactions, and stock transfers**.

## Features

- **Multi-Warehouse Stock Management**
- **Dynamic Product Variants** with automatic SKU generation
- **Stock Transactions** (Stock-in, Stock-out, and Stock Transfers)
- **Real-Time Stock Availability** per warehouse
- **User Roles & Permissions** (Spatie Permission)
- **RESTful API** for frontend and external integrations
- **SOLID Principles & Maintainability**
- **Automated Testing** using PestPHP

---

## Prerequisites

Ensure you have the following installed:

- PHP **8.1+**
- Composer
- Node.js **(v16 or higher)** & npm
- MySQL **or another compatible database**
- Git

---

## Installation

### 1. Clone the Repository

```sh
git clone https://github.com/ljsharp/inventory_manager.git
cd inventory_manager
```

---

### 2. Backend & Frontend Setup (Laravel + Inertia.js)

1. **Install PHP dependencies**:

```sh
composer install
```

2. **Install JavaScript dependencies**:

```sh
npm install
```

3. **Copy the environment file** and configure the database:

```sh
cp .env.example .env
```

4. **Generate the application key**:

```sh
php artisan key:generate
```

5. **Run migrations and seeders**:

```sh
php artisan migrate --seed
```

6. **(Optional) Run queue worker** for background tasks:

```sh
php artisan queue:work
```

7. **Start the Laravel development server**:

```sh
php artisan serve
```

8. **Compile assets and start the frontend**:

```sh
npm run dev
```

---

## API Routes

### Authentication

- `POST /login` - Login a user
- `POST /logout` - Logout a user

### Dashboard

- `GET /admin/dashboard` - Fetch system dashboard stats

### Warehouses

- `GET /admin/warehouses` - Get all warehouses
- `POST /admin/warehouses` - Create a new warehouse
- `PUT /admin/warehouses/{id}` - Update a warehouse
- `DELETE /admin/warehouses/{id}` - Delete a warehouse

### Categories

- `GET /admin/categories` - Get all categories
- `POST /admin/categories` - Create a category
- `PUT /admin/categories/{id}` - Update a category
- `DELETE /admin/categories/{id}` - Delete a category

### Products

- `GET /admin/products` - Fetch all products
- `POST /admin/products` - Create a product
- `PUT /admin/products/{id}` - Update a product
- `DELETE /admin/products/{id}` - Delete a product

### Stock Management

- `GET /admin/stocks` - View stock availability
- `GET /admin/get-stock-availability` - Get stock availability per product
- `GET /admin/get-warehouse-based-stocks/{warehouse:name}` - Get warehouse-based stocks
- `GET /admin/get-stock-transactions/{product}` - Get stock transaction histories
- `POST /admin/stock-adjustments` - Adjust stock levels
- `POST /admin/stock-transfers` - Transfer stock between warehouses

### Users & Roles

- `GET /admin/user` - List users
- `POST /admin/user` - Create a user
- `PUT /admin/user/{id}` - Update user
- `DELETE /admin/user/{id}` - Delete user
- `POST /admin/user/destroy-bulk` - Bulk delete users

- `GET /admin/role` - List roles
- `POST /admin/role` - Create a role
- `PUT /admin/role/{id}` - Update role
- `DELETE /admin/role/{id}` - Delete role

- `GET /admin/permission` - List permissions
- `POST /admin/permission` - Create a permission
- `PUT /admin/permission/{id}` - Update permission
- `DELETE /admin/permission/{id}` - Delete permission

---

## Running Tests

Run **PestPHP tests** for backend:

```sh
php artisan test
```

---

## Seeder Information

The seeders create default categories, products, warehouses, and test users.

---

## License

This project is licensed under the MIT License.
