#!/bin/bash
#
# TunisBusiness Suite - Deployment Script
# Usage: ./deploy.sh [environment]
# Environments: local, staging, production
#

set -e  # Exit on error

ENV=${1:-local}
BOLD='\033[1m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BOLD}${BLUE}"
echo "┌──────────────────────────────────────────────────────┐"
echo "│   TunisBusiness Suite - Deployment Script v1.0.0    │"
echo "│   Environment: ${ENV}                                │"
echo "└──────────────────────────────────────────────────────┘"
echo -e "${NC}"

# Step 1: Check prerequisites
echo -e "${YELLOW}Step 1/8: Checking prerequisites...${NC}"
command -v php >/dev/null 2>&1 || { echo -e "${RED}PHP is required but not installed.${NC}" >&2; exit 1; }
command -v composer >/dev/null 2>&1 || { echo -e "${RED}Composer is required but not installed.${NC}" >&2; exit 1; }
command -v docker >/dev/null 2>&1 || { echo -e "${RED}Docker is required but not installed.${NC}" >&2; exit 1; }
echo -e "${GREEN}✓ All prerequisites satisfied${NC}"

# Step 2: Install dependencies
echo -e "${YELLOW}Step 2/8: Installing dependencies...${NC}"
if [ "$ENV" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install
fi
echo -e "${GREEN}✓ Dependencies installed${NC}"

# Step 3: Environment configuration
echo -e "${YELLOW}Step 3/8: Configuring environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"

    # Generate application key
    php artisan key:generate --no-interaction
    echo -e "${GREEN}✓ Application key generated${NC}"

    if [ "$ENV" = "local" ]; then
        echo -e "${YELLOW}⚠ Please configure your .env file with database credentials${NC}"
        echo -e "${YELLOW}⚠ Then run: php artisan migrate --seed${NC}"
    fi
else
    echo -e "${GREEN}✓ .env file already exists${NC}"
fi

# Step 4: Set permissions
echo -e "${YELLOW}Step 4/8: Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"

# Step 5: Database setup
if [ "$ENV" != "production" ]; then
    echo -e "${YELLOW}Step 5/8: Setting up database...${NC}"

    read -p "Run migrations? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan migrate --force
        echo -e "${GREEN}✓ Migrations executed${NC}"

        read -p "Seed database with demo data? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            php artisan db:seed --force
            echo -e "${GREEN}✓ Database seeded${NC}"
        fi
    fi
else
    echo -e "${YELLOW}Step 5/8: Skipping database setup (production mode)${NC}"
    echo -e "${YELLOW}⚠ Run migrations manually: php artisan migrate --force${NC}"
fi

# Step 6: Optimize for environment
echo -e "${YELLOW}Step 6/8: Optimizing application...${NC}"
if [ "$ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
    echo -e "${GREEN}✓ Production optimizations applied${NC}"
else
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    echo -e "${GREEN}✓ Development caches cleared${NC}"
fi

# Step 7: Create storage link
echo -e "${YELLOW}Step 7/8: Creating storage link...${NC}"
php artisan storage:link 2>/dev/null || echo -e "${YELLOW}⚠ Storage link already exists${NC}"
echo -e "${GREEN}✓ Storage link ready${NC}"

# Step 8: Final checks
echo -e "${YELLOW}Step 8/8: Running final checks...${NC}"
php artisan about --only=environment 2>/dev/null || true
echo -e "${GREEN}✓ All checks complete${NC}"

echo -e "${BOLD}${GREEN}"
echo "┌──────────────────────────────────────────────────────┐"
echo "│   ✓ Deployment Complete!                            │"
echo "└──────────────────────────────────────────────────────┘"
echo -e "${NC}"

if [ "$ENV" = "local" ]; then
    echo -e "${BLUE}Next steps:${NC}"
    echo -e "  1. Configure your .env file"
    echo -e "  2. Run: ${BOLD}php artisan serve${NC}"
    echo -e "  3. Visit: ${BOLD}http://localhost:8000${NC}"
    echo -e ""
    echo -e "${BLUE}Test accounts:${NC}"
    echo -e "  Starter:    admin@starter.tunisbusiness.tn    / password"
    echo -e "  Business:   admin@business.tunisbusiness.tn   / password"
    echo -e "  Enterprise: admin@enterprise.tunisbusiness.tn / password"
elif [ "$ENV" = "production" ]; then
    echo -e "${YELLOW}Production Checklist:${NC}"
    echo -e "  ☐ Run migrations: ${BOLD}php artisan migrate --force${NC}"
    echo -e "  ☐ Configure queue worker"
    echo -e "  ☐ Configure scheduler cron job"
    echo -e "  ☐ Set up backups"
    echo -e "  ☐ Configure monitoring"
    echo -e "  ☐ Test health endpoints: /api/health"
fi

echo -e ""
echo -e "${GREEN}Happy coding! 🚀${NC}"
