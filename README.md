# PHP 8 Features Demo Project

A comprehensive demonstration project showcasing PHP 8 features with attribute-based routing, domain-driven design, and live refactoring examples.

## 🎯 Purpose

This project serves as a **live demonstration tool** for PHP 8 features, allowing you to:
- Show working PHP 7.4 code that can be refactored to PHP 8
- Demonstrate the benefits of modern PHP features to colleagues
- Provide hands-on examples of real-world PHP 8 improvements

## 🚀 Quick Start

### Requirements
- Docker & Docker Compose
- Git

### Installation
```bash
git clone <repository-url>
cd attributes
docker-compose up -d
```

That's it! The project runs entirely in Docker with no local PHP installation required.

### Testing
```bash
# Run all tests
./run.sh tests

# List all routes
./run.sh route:list

# View PHP 8 demo page
open http://localhost:8000/demo
```

## 🎨 PHP 8 Features Demo

Visit `http://localhost:8000/demo` for an interactive demonstration of PHP 8 features:

### 📝 Constructor Property Promotion
- **Before**: Verbose property declarations and manual assignments
- **After**: Clean, concise constructor parameters with automatic property creation

### 🔗 Union Types
- **Before**: Docblock types with manual `is_*()` validation
- **After**: Native union types (`string|int|float`) with runtime enforcement

### 🎯 Match Expression
- **Before**: Verbose switch statements with break statements
- **After**: Clean match expressions with strict comparison

### 🛡️ Nullsafe Operator
- **Before**: Nested null checks and verbose conditional logic
- **After**: Elegant `?->` operator for safe property access

Each section shows **working examples** that produce the same results before and after refactoring, making it perfect for live demonstrations.

## 🏗️ Architecture Features

### Domain-Driven Design
```
src/Domains/
├── Demo/                  # PHP 8 feature demonstrations
├── User/                  # User domain (CRUD operations)
├── Post/                  # Post domain (CRUD operations)
├── Infrastructure/        # Shared infrastructure
└── Database/             # Data access layer
```

### Attribute-Based Routing
Routes are automatically discovered using PHP 8 attributes:
```php
#[Route(method: 'GET', path: '/users', prefix: 'api')]
#[Middleware([LoggingMiddleware::class])]
public function index(): ResponseInterface
```

### Architecture Testing
Enforces domain boundaries with custom PestPHP architecture tests:
- Classes can only be used within their own domain
- Cross-domain usage requires `#[PublicClass]` attribute
- Infrastructure and Database domains are always public

## 🧪 Testing

Comprehensive test suite with **23 tests** covering:
- ✅ API endpoint functionality
- ✅ Request validation
- ✅ Error handling
- ✅ Domain architecture rules
- ✅ PHP 8 feature demonstrations

```bash
./run.sh tests
```

## 🛠️ Development Tools

### Console Commands
```bash
# List all registered routes
./run.sh route:list

# Run test suite
./run.sh tests

# Show available commands
./run.sh
```

### Available API Endpoints

#### Users API
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get user by ID  
- `POST /api/users` - Create user (validated)
- `PUT /api/users/{id}` - Update user (validated)
- `DELETE /api/users/{id}` - Delete user

#### Posts API
- `GET /api/posts` - List all posts
- `GET /api/posts/{id}` - Get post by ID
- `GET /api/posts/search?title={query}` - Search posts
- `POST /api/posts` - Create post (validated)
- `PUT /api/posts/{id}` - Update post (validated)
- `DELETE /api/posts/{id}` - Delete post

#### Demo
- `GET /demo` - Interactive PHP 8 features demonstration

## 🔧 Key Technologies

- **PHP 8.0** - Modern PHP with attributes, union types, match expressions
- **League Route** - Attribute-based HTTP routing
- **Symfony Validator** - Request validation with attributes
- **PHP-DI** - Dependency injection container
- **PestPHP** - Modern testing framework
- **SQLite** - Lightweight database (no setup required)
- **Docker** - Containerized development environment

## 🎓 Perfect for Learning & Teaching

This project is ideal for:
- **Team presentations** on PHP 8 benefits
- **Live coding demonstrations** of modern PHP features
- **Architecture discussions** about domain-driven design
- **Testing workshops** with PestPHP and architecture tests

## 📊 Project Stats

- **4 PHP 8 feature demonstrations** with working examples
- **12 API endpoints** with full CRUD operations
- **23 comprehensive tests** ensuring reliability
- **Domain architecture enforcement** with custom rules
- **Zero configuration** - runs entirely in Docker

## 🚀 Getting Started with the Demo

1. **Start the project**: `docker-compose up -d`
2. **Open the demo**: `http://localhost:8000/demo`
3. **Show the working results** to your colleagues
4. **Open the source files** in your IDE:
   - `src/Domains/Demo/PropertyPromotionDemo.php`
   - `src/Domains/Demo/UnionTypesDemo.php`
   - `src/Domains/Demo/MatchExpressionDemo.php`
   - `src/Domains/Demo/NullsafeOperatorDemo.php`
5. **Refactor live** from PHP 7.4 to PHP 8
6. **Refresh the demo page** - same results, cleaner code!

Ready to showcase the power of modern PHP! 🎉
