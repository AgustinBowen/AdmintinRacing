#!/bin/bash

# =============================================================================
#  migrate_neon.sh — Migraciones Laravel → Neon PostgreSQL + crear admin
# =============================================================================
#  Uso:
#    chmod +x migrate_neon.sh
#    ./migrate_neon.sh
#
#  Lee la configuración directamente desde el .env del proyecto.
# =============================================================================

set -e

# ── Colores ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

log()   { echo -e "${CYAN}[INFO]${NC} $1"; }
ok()    { echo -e "${GREEN}[OK]${NC}   $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
error() { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

echo ""
echo -e "${CYAN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║   AdminTin Racing — Migrate & Seed Admin     ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════╝${NC}"
echo ""

# ── 1. Verificar raíz del proyecto Laravel ───────────────────────────────────
[ -f "artisan" ] || error "No se encontró 'artisan'. Ejecutá el script desde la raíz del proyecto Laravel."
[ -f ".env"    ] || error "No se encontró el archivo .env."
ok "Proyecto Laravel detectado."

# ── 2. Verificar PHP ──────────────────────────────────────────────────────────
command -v php >/dev/null 2>&1 || error "PHP no está instalado o no está en el PATH."
ok "PHP $(php -r 'echo PHP_VERSION;') disponible."

# ── 3. Leer variables del .env ────────────────────────────────────────────────
get_env() {
  grep -E "^${1}=" .env | cut -d= -f2- | tr -d '"' | tr -d "'" | tr -d ' '
}

DB_CONNECTION=$(get_env DB_CONNECTION)
DB_HOST=$(get_env DB_HOST)
DB_PORT=$(get_env DB_PORT)
DB_DATABASE=$(get_env DB_DATABASE)
DB_USERNAME=$(get_env DB_USERNAME)
DB_PASSWORD=$(get_env DB_PASSWORD)
DB_SSLMODE=$(get_env DB_SSLMODE)

# Validar que las variables críticas existan
[ -z "$DB_HOST" ]     && error "DB_HOST no encontrado en .env"
[ -z "$DB_DATABASE" ] && error "DB_DATABASE no encontrado en .env"
[ -z "$DB_USERNAME" ] && error "DB_USERNAME no encontrado en .env"
[ -z "$DB_PASSWORD" ] && error "DB_PASSWORD no encontrado en .env"

ok "Conexión leída desde .env → ${DB_HOST}/${DB_DATABASE}"

# Exportar para que artisan las use (sobreescribe cualquier valor en caché)
export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
export DB_HOST
export DB_PORT="${DB_PORT:-5432}"
export DB_DATABASE
export DB_USERNAME
export DB_PASSWORD
export DB_SSLMODE="${DB_SSLMODE:-require}"
export PGSSLMODE="${DB_SSLMODE:-require}"

# ── 4. Instalar vendor/ si no existe ─────────────────────────────────────────
if [ ! -d "vendor" ]; then
  command -v composer >/dev/null 2>&1 || error "Composer no está instalado."
  log "Instalando dependencias con Composer..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
  ok "Dependencias instaladas."
fi

# ── 5. Limpiar caché de config para forzar los valores del .env ──────────────
log "Limpiando caché de configuración..."
php artisan config:clear --quiet
ok "Caché limpiada."

# ── 6. Correr migraciones ─────────────────────────────────────────────────────
echo ""
log "Ejecutando migraciones en Neon (${DB_DATABASE})..."
echo ""

php artisan migrate --force

echo ""
ok "Migraciones completadas."

# ── 7. Crear usuario admin ────────────────────────────────────────────────────
echo ""
log "Creando usuario admin..."

ADMIN_NAME="Admin"
ADMIN_EMAIL="admin@admintinracing.com"
ADMIN_PASSWORD="verstappen33crg"

php artisan tinker --no-interaction <<EOF

use Illuminate\Support\Facades\Hash;
use App\Models\User;

\$email    = '${ADMIN_EMAIL}';
\$password = Hash::make('${ADMIN_PASSWORD}');

\$user = User::where('email', \$email)->first();

if (\$user) {
    \$user->password = \$password;
    if (in_array('role', \$user->getFillable()))          \$user->role     = 'admin';
    if (in_array('is_admin', \$user->getFillable()))      \$user->is_admin = true;
    \$user->save();
    echo "✔ Usuario admin actualizado.\n";
} else {
    \$data = [
        'name'     => '${ADMIN_NAME}',
        'email'    => \$email,
        'password' => \$password,
    ];
    if (in_array('role', (new User)->getFillable()))      \$data['role']     = 'admin';
    if (in_array('is_admin', (new User)->getFillable()))  \$data['is_admin'] = true;

    User::create(\$data);
    echo "✔ Usuario admin creado.\n";
}

EOF

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✔ Todo listo                               ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Base de datos : ${YELLOW}${DB_DATABASE}${NC} @ ${DB_HOST}"
echo -e "  Admin email   : ${YELLOW}${ADMIN_EMAIL}${NC}"
echo -e "  Admin password: ${YELLOW}${ADMIN_PASSWORD}${NC}"
echo ""