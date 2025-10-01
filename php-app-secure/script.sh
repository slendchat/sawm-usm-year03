#!/bin/bash
set -e

echo "Stopping containers and removing volumes..."
docker-compose down -v

echo "Removing unused Docker artifacts..."
docker system prune -af

echo "Building and starting the stack with fresh migrations..."
docker-compose up --build -d
