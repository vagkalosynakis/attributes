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
- `POST /api/users` - Create user (validated)
- `PUT /api/users/{id}` - Update user (validated)
- `DELETE /api/users/{id}` - Delete user

### Posts API
- `GET /api/posts` - List all posts
- `GET /api/posts/{id}` - Get post by ID
- `POST /api/posts` - Create post (validated)
- `PUT /api/posts/{id}` - Update post (validated)
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
│   │   ├── Requests/          # Request validation
│   │   ├── Services/          # Business logic
│   │   └── Repositories/      # Data access
│   └── Post/
│       ├── Controllers/       # Post endpoints
│       ├── Requests/          # Request validation
│       ├── Services/          # Business logic
│       └── Repositories/      # Data access
```

## Features

- Attribute-based routing
- Automatic route discovery
- Domain-driven architecture
- Dependency injection
- Middleware support
- HTTP request validation

## Request Validation

POST/PUT endpoints validate request data using Symfony Validator:

### User Validation
```json
// Valid request
{
  "name": "John Doe",
  "email": "john@example.com"
}

// Invalid request returns 422
{
  "success": false,
  "error": "Validation failed",
  "validation_errors": {
    "name": "This value is too short. It should have 2 characters or more.",
    "email": "This value is not a valid email address."
  }
}
```

### Post Validation
```json
// Valid request
{
  "title": "My Great Post",
  "content": "This is a longer content that meets validation requirements",
  "user_id": 1
}

// Invalid request returns 422
{
  "success": false,
  "error": "Validation failed",
  "validation_errors": {
    "title": "This value is too short. It should have 3 characters or more.",
    "content": "This value is too short. It should have 10 characters or more.",
    "user_id": "This value should be positive."
  }
}
```
