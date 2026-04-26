#!/usr/bin/env bash
# =============================================================================
# migrate-to-saas.sh
#
# Script de migración para convertir una instalación existente de TOTORO
# al modelo multi-tenant SaaS.
#
# PREREQUISITOS:
#   - PHP 8.3+ con ext-sodium habilitado
#   - Composer dependencies instaladas (stancl/tenancy v3)
#   - .env configurado con CENTRAL_DOMAIN, DB_* y APP_KEY
#
# USO:
#   bash scripts/migrate-to-saas.sh [--dry-run] [--rollback]
#
# OPCIONES:
#   --dry-run     Muestra qué haría sin modificar datos
#   --rollback    Revierte la asignación de tenant_id (pone NULL a todos)
# =============================================================================

set -euo pipefail

PHP=${PHP_BINARY:-php}
ARTISAN="$PHP artisan"
DRY_RUN=false
ROLLBACK=false

# ── Parsear argumentos ────────────────────────────────────────────────────────
for arg in "$@"; do
    case $arg in
        --dry-run)  DRY_RUN=true ;;
        --rollback) ROLLBACK=true ;;
    esac
done

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║   TOTORO — Migración Multi-Tenant SaaS   ║"
echo "╚══════════════════════════════════════════╝"
echo ""

# ── Modo rollback ─────────────────────────────────────────────────────────────
if [ "$ROLLBACK" = true ]; then
    echo "⚠️  ROLLBACK — Revertiendo tenant_id a NULL en todas las tablas..."
    if [ "$DRY_RUN" = false ]; then
        $ARTISAN tinker --execute="
            \$tables = ['users','branches','customers','suppliers','products','categories',
                        'sales','purchases','gastos','faltantes','branch_inventory',
                        'inventories','sale_details','purchase_details','cobros',
                        'pagos_proveedor','cuentas_por_cobrar','cuentas_por_pagar',
                        'category_product','product_supplier'];
            foreach (\$tables as \$t) {
                if (Schema::hasTable(\$t)) {
                    DB::table(\$t)->update(['tenant_id' => null]);
                    echo \"  \$t → tenant_id = NULL\n\";
                }
            }
            echo \"Rollback completado.\n\";
        "
    else
        echo "   [dry-run] Se pondría tenant_id = NULL en todas las tablas de datos."
    fi
    echo "✅ Rollback completado."
    exit 0
fi

# ── Verificaciones previas ────────────────────────────────────────────────────
echo "1. Verificando entorno..."

# PHP versión
PHP_VER=$($PHP -r "echo PHP_VERSION;")
echo "   PHP: $PHP_VER"

# ext-sodium
if ! $PHP -m | grep -q sodium; then
    echo "❌ ext-sodium no está habilitado. Edita php.ini y habilita extension=sodium"
    exit 1
fi
echo "   ext-sodium: OK"

# .env existe
if [ ! -f .env ]; then
    echo "❌ No se encontró el archivo .env"
    exit 1
fi
echo "   .env: OK"

# APP_KEY configurado
APP_KEY=$(grep "^APP_KEY=" .env | cut -d= -f2)
if [ -z "$APP_KEY" ]; then
    echo "❌ APP_KEY no está configurado en .env"
    exit 1
fi
echo "   APP_KEY: OK"

echo ""
echo "2. Ejecutando migraciones pendientes..."
if [ "$DRY_RUN" = false ]; then
    $ARTISAN migrate --force
else
    echo "   [dry-run] Se ejecutaría: php artisan migrate --force"
fi

echo ""
echo "3. Creando tenant inicial y migrando datos existentes..."
if [ "$DRY_RUN" = false ]; then
    $ARTISAN db:seed --class=InitialTenantSeeder --force
else
    echo "   [dry-run] Se ejecutaría: php artisan db:seed --class=InitialTenantSeeder --force"
fi

echo ""
echo "4. Verificando aislamiento..."
if [ "$DRY_RUN" = false ]; then
    $ARTISAN tinker --execute="
        \$total  = DB::table('customers')->count();
        \$sinTenant = DB::table('customers')->whereNull('tenant_id')->count();
        echo \"  customers — total: \$total, sin tenant_id: \$sinTenant\n\";

        \$total  = DB::table('branches')->count();
        \$sinTenant = DB::table('branches')->whereNull('tenant_id')->count();
        echo \"  branches  — total: \$total, sin tenant_id: \$sinTenant\n\";

        \$tenants = DB::table('tenants')->count();
        echo \"  tenants   — total: \$tenants\n\";
    "
else
    echo "   [dry-run] Se verificaría que todos los registros tengan tenant_id asignado."
fi

echo ""
echo "5. Limpiando caché de configuración y rutas..."
if [ "$DRY_RUN" = false ]; then
    $ARTISAN config:clear
    $ARTISAN route:clear
    $ARTISAN view:clear
else
    echo "   [dry-run] Se limpiarían caches."
fi

echo ""
echo "╔══════════════════════════════════════════╗"
echo "║   ✅ Migración SaaS completada           ║"
echo "╚══════════════════════════════════════════╝"
echo ""
echo "PRÓXIMOS PASOS:"
echo "  1. Configura el CENTRAL_DOMAIN en .env (ej: totoro.mx)"
echo "  2. Accede al Super Admin: /admin/tenants"
echo "  3. Crea el primer usuario admin para el tenant inicial"
echo "  4. Configura DNS wildcard: *.totoro.mx → tu servidor"
echo ""
