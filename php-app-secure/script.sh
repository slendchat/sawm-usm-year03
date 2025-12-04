#!/bin/bash
set -e

sudo chmod +wr storage/logs

echo "Stopping containers and removing volumes..."
sudo docker stop php-app-secure-db-1
sudo docker stop php-app-secure-migrator-1
sudo docker stop php-app-secure-app-1

echo "Removing unused Docker artifacts..."
sudo docker rm php-app-secure-db-1
sudo docker rm php-app-secure-migrator-1
sudo docker rm php-app-secure-app-1

echo "Building and starting the stack with fresh migrations..."
sudo docker compose up --build -d
