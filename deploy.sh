#!/bin/bash
set -e

git pull

docker compose -f docker-compose.prod.yml up -d --build

docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:clear
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app mkdir -p storage/app/private/imports
docker compose -f docker-compose.prod.yml exec app mkdir -p storage/app/public/event/audio
docker compose -f docker-compose.prod.yml exec app chmod -R 775 storage bootstrap/cache
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data storage/app/public/event storage/app/private/imports storage/framework storage/logs bootstrap/cache

echo "Backend deployed successfully."
