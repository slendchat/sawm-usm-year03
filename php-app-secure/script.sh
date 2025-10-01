#!/bin/bash
echo "🚀 Stopping any running containers..."
docker-compose down

echo "🔄 Removing old Docker images..."
docker system prune -af

echo "📦 Building and starting the containers..."
docker-compose up --build -d
