#!/bin/bash

# Attributes Console Runner
# Usage: ./run.sh [command] [arguments...]
# Example: ./run.sh route:list

CONTAINER_NAME="attributes-web"

# Check if container is running
if ! docker ps --format "table {{.Names}}" | grep -q "^${CONTAINER_NAME}$"; then
    echo "🚨 Container '${CONTAINER_NAME}' is not running!"
    echo "💡 Start it with: docker-compose up -d"
    exit 1
fi

# If no arguments provided, show help
if [ $# -eq 0 ]; then
    echo "🚀 Attributes Console Runner"
    echo ""
    echo "Usage: ./run.sh [command] [arguments...]"
    echo ""
    echo "Examples:"
    echo "  ./run.sh route:list              # List all routes"
    echo "  ./run.sh tests                   # Run PestPHP tests"
    echo "  ./run.sh tests --verbose         # Run tests with verbose output"
    echo "  ./run.sh --help                  # Show console help"
    echo "  ./run.sh route:list --help       # Show route:list help"
    echo ""
    echo "Available commands:"
    docker exec -it "${CONTAINER_NAME}" php /app/console list --raw | grep -E "^\s+route:" | sed 's/^/  /'
    echo "  tests                            # Run PestPHP unit tests"
    exit 0
fi

# Handle special commands
case "$1" in
    "tests")
        # Remove 'tests' from arguments and pass the rest to pest
        shift
        echo "🧪 Running PestPHP tests in container..."
        docker exec -it "${CONTAINER_NAME}" ./vendor/bin/pest "$@"
        ;;
    *)
        # Execute console commands in the container
        docker exec -it "${CONTAINER_NAME}" php /app/console "$@"
        ;;
esac 