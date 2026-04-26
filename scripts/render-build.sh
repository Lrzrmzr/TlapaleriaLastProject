#!/bin/bash
set -e

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Generando claves de Passport..."
php artisan passport:keys --force 2>/dev/null || true

echo "==> Creando enlace de storage..."
php artisan storage:link 2>/dev/null || true

echo "==> Cacheando configuración..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Aplicación lista!"
