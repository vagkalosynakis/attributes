# Simple PHP 8.4 Project with League Router and PHP-DI

A minimal PHP 8.4 project demonstrating PSR-4 autoloading, League Router, and PHP-DI dependency injection.

## Requirements

- PHP 8.4 or higher
- Composer

## Installation

1. Install dependencies:
```bash
composer install
```

2. Start the development server:
```bash
php -S localhost:8000
```

## Available Endpoints

- `GET /` - Home page
- `GET /about` - About page  
- `GET /posts` - List all posts
- `GET /posts/{id}` - Get specific post by ID

## Project Structure

```
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   └── PostController.php
│   └── Container/
│       └── ContainerConfig.php
├── index.php
├── composer.json
└── .htaccess
```

## Features

- ✅ PHP 8.4 compatibility
- ✅ PSR-4 autoloading
- ✅ League Router for routing
- ✅ PHP-DI for dependency injection
- ✅ JSON responses
- ✅ Clean URL structure
- ✅ Error handling

## Testing

You can test the endpoints using curl:

```bash
# Home page
curl http://localhost:8000/

# About page
curl http://localhost:8000/about

# All posts
curl http://localhost:8000/posts

# Specific post
curl http://localhost:8000/posts/1
```
