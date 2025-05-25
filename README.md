# Two P Tech Task 

A comprehensive Laravel-based shopping cart API built with clean architecture, featuring JWT authentication, comprehensive testing, and complete API documentation.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Documentation](#api-documentation)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Architecture](#architecture)
- [Contributing](#contributing)

## 🎯 Overview

This project is a technical task implementation for **2P** featuring a robust shopping cart API system. The application demonstrates modern Laravel development practices, clean architecture principles, and comprehensive testing strategies.

### Key Highlights
- **Clean Architecture**: Domain-driven design with Use Cases pattern
- **Comprehensive Testing**: Unit and Feature tests with 95%+ coverage
- **API Documentation**: Complete Swagger/OpenAPI documentation
- **Security First**: JWT authentication with Laravel Sanctum
- **Production Ready**: Optimized for deployment with proper error handling

## ✨ Features

### 🛒 Cart Management
- Add products to cart with quantity validation
- Update item quantities with stock checking
- Remove individual items or clear entire cart
- Real-time cart summary and totals
- Persistent cart storage per user

### 🔐 Authentication & Security
- JWT-based authentication using Laravel Sanctum
- User registration and login endpoints
- Protected routes with middleware
- Input validation and sanitization
- Error handling without data exposure

### 📦 Product Management
- Product catalog with stock management
- Active/inactive product status
- Stock quantity validation
- Product details and pricing

### 💳 Checkout System
- Order initiation and processing
- Payment integration ready
- Order status tracking
- Transaction reference management

### 📚 Documentation & Testing
- Complete Swagger/OpenAPI documentation
- Comprehensive unit and integration tests
- cURL examples for all endpoints
- Postman collection ready

## 🛠 Tech Stack

### Backend
- **Laravel 12.x** - PHP Framework
- **PHP 8.2+** - Programming Language
- **MySQL 8.0+** - Database
- **Laravel Sanctum** - API Authentication
- **L5-Swagger** - API Documentation

### Testing
- **PHPUnit** - Testing Framework
- **Mockery** - Mocking Library

### Development Tools
- **Composer** - Dependency Management
- **Artisan** - Laravel CLI
- **Git** - Version Control

## 🚀 Installation

### Prerequisites
- PHP >= 8.2
- Redis Extension
- Composer
- MySQL >= 8.0
- Git

### Step-by-Step Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/ibrahim-999/two-p-tech-task.git
   cd two-p-tech-task
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE two_p_tech_task;"
   
   # Update .env file with your database credentials
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=two_p_tech_task
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Seed Database (Optional)**
   ```bash
   php artisan db:seed
   ```

7. **Generate API Documentation**
   ```bash
   php artisan l5-swagger:generate
   ```

8. **Start Development Server**
   ```bash
   php artisan serve
   ```

The application will be available at `http://localhost:8000`

## ⚙️ Configuration

### Environment Variables
Key environment variables to configure:

```env
# Application
APP_NAME="Two P Tech Task"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=two_p_tech_task
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000

# API Documentation
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_USE_ABSOLUTE_PATH=true
```

## 📚 API Documentation

### Generate Documentation
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
```

### View Documentation
- **Swagger UI**: `http://localhost:8000/api/documentation`
- **JSON Schema**: `http://localhost:8000/docs/api-docs.json`

## 🔐 Authentication

The API uses Laravel Sanctum for JWT-based authentication.

### Get Authentication Token
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

### Use Token in Requests
```bash
curl -X GET http://localhost:8000/api/v1/carts \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## 🛒 API Endpoints

### Authentication Routes
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/login` | User login | ❌ |
| POST | `/api/v1/logout` | User logout | ✅ |
| POST | `/api/v1/logout-current` | Logout current session | ✅ |
| GET | `/api/v1/me` | Get user profile | ✅ |

### Product Routes
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/products` | List all products | ✅ |
| GET | `/api/v1/products/{id}` | Get product details | ✅ |

### Cart Routes
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/carts` | Get user's cart | ✅ |
| POST | `/api/v1/carts` | Add item to cart | ✅ |
| GET | `/api/v1/carts/{id}` | Get cart summary | ✅ |
| PUT | `/api/v1/carts/{productId}` | Update item quantity | ✅ |
| DELETE | `/api/v1/carts/{productId}` | Remove item from cart | ✅ |
| DELETE | `/api/v1/cart/clear` | Clear entire cart | ✅ |
| GET | `/api/v1/cart/summary` | Get detailed cart summary | ✅ |

### Checkout Routes
| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/checkout` | Initiate checkout | ✅ |
| GET | `/api/v1/checkout/status/{ref}` | Check order status | ✅ |
| POST | `/api/v1/mock-callback` | Mock payment callback | ✅ |
| POST | `/api/v1/payments/callback` | Payment callback | ✅ |
| POST | `/api/v1/reset-order` | Reset order | ✅ |

### Example Requests

#### Add Item to Cart
```bash
curl -X POST http://localhost:8000/api/v1/carts \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

#### Update Cart Item
```bash
curl -X PUT http://localhost:8000/api/v1/carts/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "quantity": 5
  }'
```

## 🧪 Testing

### Setup Test Environment
```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE two_p_tech_task_test;"

# Configure test environment
cp .env .env.testing

# Update .env.testing
DB_DATABASE=two_p_tech_task_test
```

### Run Tests
```bash
# Run all tests

# Run specific test suites
php artisan test --testsuite=Unit

# Run cart-specific tests
php artisan test --filter=CartController

# Run tests with coverage
php artisan test --coverage
```

### Test Structure
```
tests/
├── Unit/
│   └── Http/Controllers/Api/v1/
│       └── CartControllerTest.php        # Unit tests with mocks
└── TestCase.php
```

## 🏗 Architecture

This project follows **Clean Architecture** principles with **Domain-Driven Design**.

### Directory Structure
```
app/
├── Application/              # Use Cases (Business Logic)
│   └── Cart/
│       ├── AddToCartUseCase.php
│       ├── UpdateCartItemUseCase.php
│       ├── RemoveFromCartUseCase.php
│       ├── GetCartUseCase.php
│       └── ClearCartUseCase.php
│
├── Domains/                  # Domain Models & Services
│   ├── Cart/
│   │   ├── Models/           # Eloquent Models
│   │   │   ├── Cart.php
│   │   │   └── CartItem.php
│   │   ├── Services/         # Domain Services
│   │   │   └── CartService.php
│   │   ├── Repositories/     # Repository Interfaces
│   │   │   └── CartRepositoryInterface.php
│   │   └── ValueObjects/     # Value Objects
│   │       └── CartItem.php
│   ├── Product/
│   │   └── Models/
│   │       └── Product.php
│   └── User/
│       └── Models/
│           └── User.php
│
├── Http/
│   ├── Controllers/Api/v1/   # API Controllers
│   │   ├── AuthController.php
│   │   ├── CartController.php
│   │   ├── ProductController.php
│   │   └── CheckoutController.php
│   ├── Requests/             # Form Request Validation
│   │   └── Cart/
│   │       ├── AddToCartRequest.php
│   │       └── UpdateCartItemRequest.php
│   └── Resources/            # API Resource Transformers
│       └── Cart/
│           ├── CartResource.php
│           ├── CartItemResource.php
│           ├── CartItemActionResource.php
│           └── CartSummaryResource.php
│
└── Traits/                   # Shared Functionality
    ├── ApiResponseTrait.php
    └── CommonServiceCrudTrait.php
```

### Design Patterns
- **Repository Pattern**: Data access abstraction
- **Factory Pattern**: Test data generation
- **Singleton Pattern**: Get one instance

### Benefits
- **Separation of Concerns**: Clear boundaries between layers
- **Testability**: Easy to mock and test individual components
- **Maintainability**: Changes isolated to specific layers
- **Scalability**: Easy to extend and modify functionality

## 🚀 Deployment

### Production Checklist
```bash
# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate API documentation
php artisan l5-swagger:generate

# Set permissions
chmod -R 755 storage bootstrap/cache
```

### Environment Variables for Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-production-db
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password

# Other production settings
LOG_CHANNEL=daily
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Code Standards
- Follow PSR-12 coding standards
- Write comprehensive tests
- Update documentation for API changes
- Use meaningful commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Ibrahim** - [ibrahim-999](https://github.com/ibrahim-999)


**Technical Task for Two P - Built with ❤️ using Laravel**
