#!/bin/bash
# ─────────────────────────────────────────────────────────────────────────────
# HOSTINGER DEPLOYMENT SCRIPT — WebworkInvoice
# ─────────────────────────────────────────────────────────────────────────────
#
# Usage:
#   SSH into Hostinger, then run:
#   chmod +x ~/deploy.sh    (first time only)
#   ~/deploy.sh             (every time you push changes)
#
# Prerequisites:
#   1. Your Laravel project is cloned at ~/webworkinvoice/
#   2. Your .env file is configured on the server at ~/webworkinvoice/.env
#   3. The public_html directory has the Hostinger index.php (see hostinger_public_html/)
#
# ─────────────────────────────────────────────────────────────────────────────

set -e

# ── Configuration ────────────────────────────────────────────────────────────
PROJECT_DIR="$HOME/webworkinvoice"
PUBLIC_DIR="$HOME/public_html"
BRANCH="main"

# ── Colors for output ───────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}   🚀 WebworkInvoice — Hostinger Deployment${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# ── Step 1: Verify project directory exists ──────────────────────────────────
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}✗ Project directory not found: $PROJECT_DIR${NC}"
    echo -e "${YELLOW}  Run first: git clone https://github.com/khanfaiz0049/webworkinvoice.git ~/webworkinvoice${NC}"
    exit 1
fi

cd "$PROJECT_DIR"

# ── Step 2: Pull latest code ────────────────────────────────────────────────
echo -e "${YELLOW}⏳ Pulling latest code from $BRANCH...${NC}"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"
echo -e "${GREEN}✓ Code updated.${NC}"

# ── Step 3: Install PHP dependencies ────────────────────────────────────────
echo -e "${YELLOW}⏳ Installing Composer dependencies...${NC}"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction --quiet
else
    php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction --quiet 2>/dev/null || \
    php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction --quiet
fi
echo -e "${GREEN}✓ Dependencies installed.${NC}"

# ── Step 4: Verify .env exists ──────────────────────────────────────────────
if [ ! -f "$PROJECT_DIR/.env" ]; then
    echo -e "${RED}✗ .env file missing!${NC}"
    echo -e "${YELLOW}  Create it: cp .env.example .env && nano .env${NC}"
    exit 1
fi

# ── Step 5: Run database migrations ─────────────────────────────────────────
echo -e "${YELLOW}⏳ Running database migrations...${NC}"
php artisan migrate --force --no-interaction
echo -e "${GREEN}✓ Migrations complete.${NC}"

# ── Step 6: Clear and rebuild caches (with SERVER paths) ────────────────────
echo -e "${YELLOW}⏳ Rebuilding caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Caches rebuilt with server paths.${NC}"

# ── Step 7: Sync public assets to public_html ───────────────────────────────
echo -e "${YELLOW}⏳ Syncing public assets...${NC}"

# Create build directory if it doesn't exist
mkdir -p "$PUBLIC_DIR/build"

# Copy build assets (CSS/JS from Vite)
if [ -d "$PROJECT_DIR/public/build" ]; then
    cp -r "$PROJECT_DIR/public/build/"* "$PUBLIC_DIR/build/" 2>/dev/null || true
fi

# Copy static files
cp "$PROJECT_DIR/public/favicon.ico" "$PUBLIC_DIR/favicon.ico" 2>/dev/null || true
cp "$PROJECT_DIR/public/robots.txt" "$PUBLIC_DIR/robots.txt" 2>/dev/null || true

# Copy the Hostinger-specific index.php and .htaccess
if [ -d "$PROJECT_DIR/hostinger_public_html" ]; then
    cp "$PROJECT_DIR/hostinger_public_html/index.php" "$PUBLIC_DIR/index.php"
    cp "$PROJECT_DIR/hostinger_public_html/.htaccess" "$PUBLIC_DIR/.htaccess"
fi

# Create storage symlink if it doesn't exist
if [ ! -L "$PUBLIC_DIR/storage" ]; then
    ln -sf "$PROJECT_DIR/storage/app/public" "$PUBLIC_DIR/storage"
    echo -e "${GREEN}✓ Storage symlink created.${NC}"
fi

echo -e "${GREEN}✓ Public assets synced.${NC}"

# ── Step 8: Set file permissions ────────────────────────────────────────────
echo -e "${YELLOW}⏳ Setting permissions...${NC}"
chmod -R 755 "$PROJECT_DIR/storage" "$PROJECT_DIR/bootstrap/cache"
chmod -R 775 "$PROJECT_DIR/storage/logs"
echo -e "${GREEN}✓ Permissions set.${NC}"

# ── Done! ────────────────────────────────────────────────────────────────────
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}   ✅ Deployment complete!${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "  Project:    $PROJECT_DIR"
echo -e "  Public:     $PUBLIC_DIR"
echo -e "  Branch:     $BRANCH"
echo -e "  Time:       $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
