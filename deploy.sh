#!/bin/bash
set -e

git pull

docker compose -f docker-compose.prod.yml up -d --build

docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:clear
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear

echo "Backend deployed successfully."
