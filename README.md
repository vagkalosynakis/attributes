# PHP 8.0 Project with Attribute-Based Routing

Domain-driven design project with automatic route discovery using PHP attributes.

## Requirements

- PHP 8.0+
- Composer

## Installation

```bash
composer install
```

## Available Endpoints

### Users API
- `GET /api/users` - List all users
- `GET /api/users/{id}` - Get user by ID
- `POST /api/users` - Create user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### Posts API
- `GET /api/posts` - List all posts
- `GET /api/posts/{id}` - Get post by ID
- `POST /api/posts` - Create post
- `PUT /api/posts/{id}` - Update post
- `DELETE /api/posts/{id}` - Delete post

## Project Structure

```
src/
├── Domains/
│   ├── Infrastructure/
│   │   ├── Attributes/        # Route, Middleware attributes
│   │   ├── Container/         # DI configuration
│   │   ├── Services/          # Route discovery
│   │   └── Middleware/        # HTTP middleware
│   ├── User/
│   │   ├── Controllers/       # User endpoints
│   │   ├── Services/          # Business logic
│   │   └── Repositories/      # Data access
│   └── Post/
│       ├── Controllers/       # Post endpoints
│       ├── Services/          # Business logic
│       └── Repositories/      # Data access
```

## Features

- Attribute-based routing
- Automatic route discovery
- Domain-driven architecture
- Dependency injection
- Middleware support
