.PHONY: help install setup dev test clean deploy docker-up docker-down docker-logs backup optimize fresh

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m # No Color

help: ## Show this help message
	@echo '$(BLUE)TunisBusiness Suite - Available Commands$(NC)'
	@echo ''
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ''

## Setup Commands

install: ## Install dependencies
	@echo '$(YELLOW)Installing dependencies...$(NC)'
	composer install
	npm install --silent 2>/dev/null || true
	@echo '$(GREEN)✓ Dependencies installed$(NC)'

setup: install ## Complete initial setup
	@echo '$(YELLOW)Setting up application...$(NC)'
	cp -n .env.example .env || true
	php artisan key:generate
	chmod -R 775 storage bootstrap/cache
	php artisan storage:link
	@echo '$(GREEN)✓ Setup complete$(NC)'
	@echo '$(YELLOW)Next: Configure .env and run "make fresh"$(NC)'

fresh: ## Fresh install with migrations and seeding
	@echo '$(YELLOW)Fresh installation...$(NC)'
	php artisan migrate:fresh --seed
	@echo '$(GREEN)✓ Database reset and seeded$(NC)'

## Development Commands

dev: ## Start development server
	@echo '$(BLUE)Starting development server...$(NC)'
	php artisan serve

queue: ## Start queue worker
	@echo '$(BLUE)Starting queue worker...$(NC)'
	php artisan queue:work --tries=3

schedule: ## Run scheduler (for testing)
	@echo '$(BLUE)Running scheduler...$(NC)'
	php artisan schedule:run

watch: ## Watch for file changes (if using Vite)
	@npm run dev 2>/dev/null || echo '$(YELLOW)npm not configured$(NC)'

## Testing Commands

test: ## Run all tests
	@echo '$(YELLOW)Running tests...$(NC)'
	php artisan test

test-coverage: ## Run tests with coverage
	@echo '$(YELLOW)Running tests with coverage...$(NC)'
	php artisan test --coverage

test-parallel: ## Run tests in parallel
	@echo '$(YELLOW)Running parallel tests...$(NC)'
	php artisan test --parallel

test-filter: ## Run specific test (usage: make test-filter TEST=HealthCheckTest)
	@echo '$(YELLOW)Running $(TEST)...$(NC)'
	php artisan test --filter=$(TEST)

## Docker Commands

docker-up: ## Start Docker containers
	@echo '$(YELLOW)Starting Docker containers...$(NC)'
	docker compose up -d
	@echo '$(GREEN)✓ Containers started$(NC)'
	@echo 'Run "make docker-logs" to view logs'

docker-down: ## Stop Docker containers
	@echo '$(YELLOW)Stopping Docker containers...$(NC)'
	docker compose down
	@echo '$(GREEN)✓ Containers stopped$(NC)'

docker-restart: ## Restart Docker containers
	@echo '$(YELLOW)Restarting Docker containers...$(NC)'
	docker compose restart
	@echo '$(GREEN)✓ Containers restarted$(NC)'

docker-logs: ## View Docker logs
	docker compose logs -f

docker-shell: ## Access app container shell
	docker compose exec app sh

docker-mysql: ## Access database shell
	docker compose exec postgres psql -U postgres -d facturation

docker-fresh: docker-down docker-up ## Fresh Docker setup
	@echo '$(YELLOW)Waiting for containers to start...$(NC)'
	sleep 5
	docker compose exec app php artisan migrate:fresh --seed
	@echo '$(GREEN)✓ Docker setup complete$(NC)'

## Database Commands

migrate: ## Run migrations
	@echo '$(YELLOW)Running migrations...$(NC)'
	php artisan migrate
	@echo '$(GREEN)✓ Migrations complete$(NC)'

migrate-fresh: ## Fresh migrations
	@echo '$(YELLOW)Running fresh migrations...$(NC)'
	php artisan migrate:fresh
	@echo '$(GREEN)✓ Fresh migrations complete$(NC)'

seed: ## Seed database
	@echo '$(YELLOW)Seeding database...$(NC)'
	php artisan db:seed
	@echo '$(GREEN)✓ Seeding complete$(NC)'

migrate-rollback: ## Rollback last migration
	@echo '$(YELLOW)Rolling back migrations...$(NC)'
	php artisan migrate:rollback
	@echo '$(GREEN)✓ Rollback complete$(NC)'

migrate-status: ## Show migration status
	php artisan migrate:status

## Maintenance Commands

cache: ## Cache everything
	@echo '$(YELLOW)Caching configuration, routes, and views...$(NC)'
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan event:cache
	@echo '$(GREEN)✓ All caches created$(NC)'

clear: ## Clear all caches
	@echo '$(YELLOW)Clearing all caches...$(NC)'
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	php artisan cache:clear
	@echo '$(GREEN)✓ All caches cleared$(NC)'

optimize: cache ## Optimize for production
	@echo '$(YELLOW)Optimizing for production...$(NC)'
	composer dump-autoload --optimize
	php artisan optimize
	@echo '$(GREEN)✓ Optimization complete$(NC)'

backup: ## Run backup
	@echo '$(YELLOW)Running backup...$(NC)'
	php artisan backup:run
	@echo '$(GREEN)✓ Backup complete$(NC)'

clean: clear ## Deep clean (caches, logs, compiled)
	@echo '$(YELLOW)Deep cleaning...$(NC)'
	rm -rf bootstrap/cache/*.php
	rm -rf storage/logs/*.log
	rm -rf storage/framework/cache/data/*
	rm -rf storage/framework/sessions/*
	rm -rf storage/framework/views/*
	@echo '$(GREEN)✓ Deep clean complete$(NC)'

## Quality Commands

lint: ## Run code linter
	@./vendor/bin/phpstan analyse --memory-limit=2G 2>/dev/null || echo '$(YELLOW)PHPStan not installed$(NC)'

format: ## Format code
	@./vendor/bin/php-cs-fixer fix 2>/dev/null || echo '$(YELLOW)PHP-CS-Fixer not installed$(NC)'

analyze: lint ## Alias for lint

## Deployment Commands

deploy-local: ## Deploy to local environment
	@./deploy.sh local

deploy-staging: ## Deploy to staging
	@./deploy.sh staging

deploy-production: ## Deploy to production
	@./deploy.sh production

## Utility Commands

routes: ## List all routes
	php artisan route:list

tinker: ## Open Laravel tinker
	php artisan tinker

health: ## Check application health
	@curl -s http://localhost:8000/api/health | jq || curl -s http://localhost:8000/api/health

health-detailed: ## Check detailed health
	@curl -s http://localhost:8000/api/health/detailed | jq || curl -s http://localhost:8000/api/health/detailed

permissions: ## Fix storage permissions
	@echo '$(YELLOW)Fixing permissions...$(NC)'
	chmod -R 775 storage bootstrap/cache
	@echo '$(GREEN)✓ Permissions fixed$(NC)'

ide-helper: ## Generate IDE helper files
	@php artisan ide-helper:generate 2>/dev/null || echo '$(YELLOW)ide-helper not installed$(NC)'
	@php artisan ide-helper:models -N 2>/dev/null || true
	@php artisan ide-helper:meta 2>/dev/null || true

## Quick Commands

dev-full: fresh dev ## Fresh install and start server

prod: optimize ## Prepare for production

reset: clear migrate-fresh seed ## Full reset with fresh data

update: ## Update dependencies
	@echo '$(YELLOW)Updating dependencies...$(NC)'
	composer update
	npm update --silent 2>/dev/null || true
	@echo '$(GREEN)✓ Dependencies updated$(NC)'
