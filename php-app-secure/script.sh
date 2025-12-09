#!/bin/bash
set -e

echo "configuring log dir..."
sudo mkdir -p storage/logs
sudo chmod a+wr storage/logs

echo "Building and starting the stack with fresh migrations..."
sudo docker compose up --build -d
