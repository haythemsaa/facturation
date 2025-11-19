# 🚀 Guide de Déploiement Production - TunisBusiness Suite

Ce guide complet vous accompagne dans le déploiement de TunisBusiness Suite en environnement de production, du serveur bare-metal aux solutions cloud.

**Version**: 1.0.0
**Dernière mise à jour**: 19 Novembre 2025

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Options de Déploiement](#options-de-déploiement)
3. [Déploiement Docker (Recommandé)](#déploiement-docker-recommandé)
4. [Déploiement Traditionnel](#déploiement-traditionnel)
5. [Configuration SSL](#configuration-ssl)
6. [Optimisations Production](#optimisations-production)
7. [Monitoring & Logging](#monitoring--logging)
8. [Backups & Disaster Recovery](#backups--disaster-recovery)
9. [Scaling & High Availability](#scaling--high-availability)
10. [Troubleshooting](#troubleshooting)

---

## Prérequis

### Serveur Minimum

| Composant | Minimum | Recommandé |
|-----------|---------|------------|
| **OS** | Ubuntu 20.04 LTS | Ubuntu 22.04 LTS |
| **CPU** | 2 vCPUs | 4 vCPUs |
| **RAM** | 4 GB | 8 GB |
| **Stockage** | 40 GB SSD | 100 GB SSD |
| **Bande passante** | 100 Mbps | 1 Gbps |

### Logiciels Requis

#### Option Docker
- Docker 24.0+
- Docker Compose 2.20+
- Git 2.30+

#### Option Traditionnelle
- PHP 8.2+
- PostgreSQL 15+
- Redis 7+
- Nginx 1.24+ ou Apache 2.4+
- Composer 2.5+
- Supervisor 4.2+
- Certbot (pour SSL Let's Encrypt)

### Accès & Permissions

```bash
# Créer utilisateur dédié
sudo adduser --disabled-password --gecos "" tunisbusiness
sudo usermod -aG sudo,docker tunisbusiness

# Connexion
sudo su - tunisbusiness
```

---

## Options de Déploiement

### 🐳 Option 1: Docker (Recommandé)
- ✅ Configuration isolée
- ✅ Déploiement rapide (< 10 min)
- ✅ Facile à mettre à jour
- ✅ Scaling horizontal simple

### 🔧 Option 2: Traditionnel (Bare Metal)
- ✅ Performances maximales
- ✅ Contrôle total
- ⚠️ Configuration plus complexe
- ⚠️ Maintenance manuelle

### ☁️ Option 3: Cloud Providers
- **AWS**: EC2 + RDS + ElastiCache
- **DigitalOcean**: Droplet + Managed Databases
- **Azure**: App Service + Azure Database
- **OVHcloud**: VPS + Cloud Databases

---

## Déploiement Docker (Recommandé)

### Étape 1: Préparer le Serveur

```bash
# Mettre à jour le système
sudo apt update && sudo apt upgrade -y

# Installer Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER

# Installer Docker Compose
sudo apt install docker-compose-plugin -y

# Vérifier installations
docker --version
docker compose version
```

### Étape 2: Cloner le Repository

```bash
# Cloner depuis GitHub
git clone https://github.com/haythemsaa/facturation.git
cd facturation

# Créer répertoires persistants
sudo mkdir -p /var/lib/tunisbusiness/{postgres,redis}
sudo mkdir -p /var/log/tunisbusiness/{app,nginx}
sudo chown -R 82:82 /var/lib/tunisbusiness /var/log/tunisbusiness
```

### Étape 3: Configuration Environnement

```bash
# Copier et modifier .env
cp .env.production.example .env
nano .env

# Variables critiques à modifier:
# - APP_KEY (générer avec: docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32));")
# - APP_URL (votre domaine)
# - DB_PASSWORD (mot de passe fort)
# - REDIS_PASSWORD (mot de passe fort)
# - MAIL_* (configuration SMTP)
```

### Étape 4: Configuration SSL

```bash
# Option A: Let's Encrypt (Gratuit)
sudo apt install certbot -y
sudo certbot certonly --standalone -d api.tunisbusiness.tn

# Copier certificats pour Docker
sudo mkdir -p docker/nginx/ssl
sudo cp /etc/letsencrypt/live/api.tunisbusiness.tn/fullchain.pem docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/api.tunisbusiness.tn/privkey.pem docker/nginx/ssl/
sudo chmod 644 docker/nginx/ssl/*.pem

# Option B: Certificat commercial
# Placer fullchain.pem et privkey.pem dans docker/nginx/ssl/
```

### Étape 5: Modifier Nginx Config

```bash
# Éditer nginx.prod.conf
nano docker/nginx/nginx.prod.conf

# Remplacer "server_name _;" par votre domaine:
# server_name api.tunisbusiness.tn;
```

### Étape 6: Build & Launch

```bash
# Build les images
docker compose -f docker-compose.production.yml build --no-cache

# Démarrer les services
docker compose -f docker-compose.production.yml up -d

# Vérifier que tout tourne
docker compose -f docker-compose.production.yml ps
```

### Étape 7: Migrations & Seeds

```bash
# Exécuter migrations
docker compose -f docker-compose.production.yml exec app php artisan migrate --force

# (Optionnel) Seeds pour démo
docker compose -f docker-compose.production.yml exec app php artisan db:seed --force

# Optimisations Laravel
docker compose -f docker-compose.production.yml exec app php artisan optimize
docker compose -f docker-compose.production.yml exec app php artisan config:cache
docker compose -f docker-compose.production.yml exec app php artisan route:cache
docker compose -f docker-compose.production.yml exec app php artisan view:cache
```

### Étape 8: Test de Santé

```bash
# Health check
curl https://api.tunisbusiness.tn/health

# Résultat attendu:
# {"status":"healthy","timestamp":"2025-11-19T12:00:00Z"}

# Test login
curl -X POST https://api.tunisbusiness.tn/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.tn","password":"password"}'
```

### Étape 9: Auto-start au Reboot

```bash
# Docker démarre automatiquement
sudo systemctl enable docker

# Certificat SSL auto-renewal
sudo crontab -e
# Ajouter:
0 3 * * * certbot renew --quiet && docker compose -f /home/tunisbusiness/facturation/docker-compose.production.yml restart nginx
```

---

## Déploiement Traditionnel

### Étape 1: Installer les Dépendances

```bash
# Ajouter repository PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Installer PHP et extensions
sudo apt install -y php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
  php8.2-intl php8.2-bcmath php8.2-opcache

# Installer PostgreSQL
sudo apt install -y postgresql-15 postgresql-contrib

# Installer Redis
sudo apt install -y redis-server

# Installer Nginx
sudo apt install -y nginx

# Installer Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installer Supervisor
sudo apt install -y supervisor
```

### Étape 2: Configuration PostgreSQL

```bash
# Se connecter à PostgreSQL
sudo -u postgres psql

-- Créer base et utilisateur
CREATE DATABASE facturation_prod;
CREATE USER tunisbusiness_user WITH ENCRYPTED PASSWORD 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON DATABASE facturation_prod TO tunisbusiness_user;
\q

# Autoriser connexions locales
sudo nano /etc/postgresql/15/main/pg_hba.conf
# Ajouter:
# local   facturation_prod    tunisbusiness_user    md5

# Redémarrer PostgreSQL
sudo systemctl restart postgresql
```

### Étape 3: Configuration Redis

```bash
# Éditer config Redis
sudo nano /etc/redis/redis.conf

# Modifier:
# bind 127.0.0.1
# requirepass YOUR_REDIS_PASSWORD
# maxmemory 512mb
# maxmemory-policy allkeys-lru

# Redémarrer Redis
sudo systemctl restart redis-server
```

### Étape 4: Cloner et Installer

```bash
# Cloner
cd /var/www
sudo git clone https://github.com/haythemsaa/facturation.git tunisbusiness
cd tunisbusiness

# Permissions
sudo chown -R www-data:www-data /var/www/tunisbusiness
sudo chmod -R 755 /var/www/tunisbusiness/storage /var/www/tunisbusiness/bootstrap/cache

# Installer dépendances
composer install --no-dev --optimize-autoloader

# Configuration
cp .env.production.example .env
php artisan key:generate
nano .env  # Modifier variables
```

### Étape 5: Configuration Nginx

```bash
# Créer vhost
sudo nano /etc/nginx/sites-available/tunisbusiness

# Contenu (voir docker/nginx/nginx.prod.conf pour référence complète)
server {
    listen 80;
    server_name api.tunisbusiness.tn;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.tunisbusiness.tn;
    root /var/www/tunisbusiness/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/api.tunisbusiness.tn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.tunisbusiness.tn/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Activer site
sudo ln -s /etc/nginx/sites-available/tunisbusiness /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Étape 6: Configuration Supervisor (Queue)

```bash
# Créer config queue worker
sudo nano /etc/supervisor/conf.d/tunisbusiness-worker.conf

[program:tunisbusiness-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/tunisbusiness/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/tunisbusiness/storage/logs/worker.log
stopwaitsecs=3600

# Créer config scheduler
sudo nano /etc/supervisor/conf.d/tunisbusiness-scheduler.conf

[program:tunisbusiness-scheduler]
process_name=%(program_name)s
command=/bin/bash -c 'while true; do php /var/www/tunisbusiness/artisan schedule:run; sleep 60; done'
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/tunisbusiness/storage/logs/scheduler.log

# Recharger Supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### Étape 7: Migrations & Optimisation

```bash
cd /var/www/tunisbusiness

# Migrations
php artisan migrate --force

# Optimisations
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link
php artisan storage:link
```

---

## Configuration SSL

### Let's Encrypt (Gratuit)

```bash
# Installation
sudo apt install certbot python3-certbot-nginx -y

# Obtenir certificat
sudo certbot --nginx -d api.tunisbusiness.tn

# Auto-renewal (cron)
sudo crontab -e
# Ajouter:
0 3 * * * certbot renew --quiet
```

### Certificat Commercial

```bash
# 1. Acheter certificat (Comodo, DigiCert, etc.)
# 2. Recevoir fullchain.pem et privkey.pem
# 3. Placer dans /etc/ssl/tunisbusiness/
# 4. Mettre à jour nginx.conf avec chemins corrects
```

---

## Optimisations Production

### PHP (php.ini)

```ini
; /etc/php/8.2/fpm/php.ini

; Performance
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.interned_strings_buffer=16

; Limits
memory_limit=512M
upload_max_filesize=50M
post_max_size=50M
max_execution_time=300

; Production
expose_php=Off
display_errors=Off
log_errors=On
error_log=/var/log/php/error.log
```

### PostgreSQL Tuning

```sql
-- /etc/postgresql/15/main/postgresql.conf

max_connections = 100
shared_buffers = 2GB
effective_cache_size = 6GB
maintenance_work_mem = 512MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
work_mem = 5242kB
min_wal_size = 1GB
max_wal_size = 4GB
```

### Redis Optimization

```conf
# /etc/redis/redis.conf

maxmemory 512mb
maxmemory-policy allkeys-lru
save ""
appendonly yes
appendfsync everysec
```

### Laravel Optimization

```bash
# Après chaque déploiement
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

---

## Monitoring & Logging

### Log Locations

**Docker:**
```bash
# Application
docker compose -f docker-compose.production.yml logs -f app

# Nginx
docker compose -f docker-compose.production.yml logs -f nginx

# Queue
docker compose -f docker-compose.production.yml logs -f queue
```

**Traditionnel:**
```bash
# Application
tail -f /var/www/tunisbusiness/storage/logs/laravel.log

# Nginx
tail -f /var/log/nginx/tunisbusiness-access.log
tail -f /var/log/nginx/tunisbusiness-error.log

# PHP-FPM
tail -f /var/log/php8.2-fpm.log

# Supervisor
tail -f /var/www/tunisbusiness/storage/logs/worker.log
```

### Health Checks

```bash
# API Health
curl https://api.tunisbusiness.tn/health

# Detailed Health
curl https://api.tunisbusiness.tn/health/detailed

# Metrics
curl https://api.tunisbusiness.tn/health/metrics
```

### Monitoring Tools (Recommandé)

- **Sentry**: Error tracking
- **New Relic**: APM
- **Datadog**: Infrastructure monitoring
- **Uptime Robot**: Uptime monitoring
- **Grafana + Prometheus**: Métriques custom

---

## Backups & Disaster Recovery

### Backup Automatique (Intégré)

```bash
# Activé par défaut via scheduler
# Exécution quotidienne à 02:00 (Africa/Tunis)
# Backup PostgreSQL + fichiers storage
# Rétention: 30 jours

# Vérifier backup
php artisan backup:run
ls -lh storage/app/backups/
```

### Backup Manuel

```bash
# Database
pg_dump -U tunisbusiness_user facturation_prod > backup_$(date +%Y%m%d).sql

# Fichiers
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Copier vers S3 ou serveur distant
aws s3 cp backup_$(date +%Y%m%d).sql s3://tunisbusiness-backups/
```

### Restore Procedure

```bash
# 1. Arrêter application
docker compose -f docker-compose.production.yml down

# 2. Restore database
psql -U tunisbusiness_user -d facturation_prod < backup_20251119.sql

# 3. Restore fichiers
tar -xzf storage_backup_20251119.tar.gz

# 4. Redémarrer
docker compose -f docker-compose.production.yml up -d
```

---

## Scaling & High Availability

### Horizontal Scaling

```bash
# Augmenter nombre d'instances
docker compose -f docker-compose.production.yml up -d --scale app=3 --scale queue=5

# Load balancer (Nginx upstream)
upstream php-fpm {
    least_conn;
    server app1:9000 weight=3;
    server app2:9000 weight=3;
    server app3:9000 weight=2;
}
```

### Database Replication

```sql
-- Master-Slave PostgreSQL
-- Configurer streaming replication
-- Utiliser pgpool pour load balancing

-- Connexions lecture seule vers slaves
DB_READ_HOST=postgres-slave.internal
```

### Redis Cluster

```bash
# Redis Sentinel pour HA
# Ou Redis Cluster pour sharding
REDIS_CLUSTER_ENABLED=true
REDIS_CLUSTER_NODES=redis1:6379,redis2:6379,redis3:6379
```

### CDN (Cloudflare)

```bash
# Pour assets statiques
# Configure dans .env:
ASSET_URL=https://cdn.tunisbusiness.tn
```

---

## Troubleshooting

### Application ne démarre pas

```bash
# Vérifier logs
docker compose -f docker-compose.production.yml logs app

# Problèmes courants:
# - APP_KEY manquant: php artisan key:generate
# - Permissions storage: chmod -R 775 storage/
# - Database connexion: vérifier DB_* dans .env
```

### Erreurs 502 Bad Gateway

```bash
# Vérifier PHP-FPM
sudo systemctl status php8.2-fpm

# Augmenter timeout nginx
fastcgi_read_timeout 300;

# Augmenter memory_limit PHP
memory_limit=512M
```

### Queue non traitée

```bash
# Vérifier workers
docker compose -f docker-compose.production.yml ps queue
# Ou
sudo supervisorctl status tunisbusiness-worker

# Restart workers
sudo supervisorctl restart tunisbusiness-worker:*

# Purger failed jobs
php artisan queue:flush
php artisan queue:retry all
```

### Performance lente

```bash
# Cache queries
php artisan cache:clear
php artisan config:cache

# Analyser logs
# Chercher requêtes lentes dans logs PostgreSQL

# Vérifier Redis
redis-cli
> INFO stats
> SLOWLOG GET 10
```

### Espace disque plein

```bash
# Nettoyer logs
docker system prune -a
find /var/log -type f -name "*.log" -mtime +30 -delete

# Nettoyer backups anciens
find storage/app/backups -type f -mtime +30 -delete

# Nettoyer cache Laravel
php artisan cache:clear
php artisan view:clear
```

---

## Checklist Finale

### Avant Mise en Production

- [ ] `.env` configuré avec valeurs production
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` généré
- [ ] Certificat SSL installé et valide
- [ ] Firewall configuré (ports 80, 443, 22 uniquement)
- [ ] Base de données migrée (`php artisan migrate --force`)
- [ ] Cache Laravel optimisé (`php artisan optimize`)
- [ ] Backup automatique activé et testé
- [ ] Queue workers en cours d'exécution
- [ ] Scheduler opérationnel
- [ ] Health checks fonctionnels
- [ ] Monitoring configuré (Sentry, New Relic, etc.)
- [ ] DNS configuré (api.tunisbusiness.tn)
- [ ] SMTP configuré et testé (envoi emails)
- [ ] Limites de taux (rate limiting) activées
- [ ] CORS configuré correctement
- [ ] Tests end-to-end passés

### Post-Déploiement

- [ ] Vérifier `/health` endpoint
- [ ] Tester login API
- [ ] Créer tenant de test
- [ ] Vérifier envoi emails
- [ ] Tester exports PDF/Excel
- [ ] Vérifier logs pour erreurs
- [ ] Monitoring dashboards actifs
- [ ] Documentation équipe mise à jour

---

## Support

**Email**: support@tunisbusiness.tn
**Documentation**: https://docs.tunisbusiness.tn
**Status Page**: https://status.tunisbusiness.tn
**GitHub Issues**: https://github.com/haythemsaa/facturation/issues

---

**Licence**: MIT
**Version**: 1.0.0
**Auteur**: TunisBusiness Suite Team
**Dernière mise à jour**: 19 Novembre 2025
