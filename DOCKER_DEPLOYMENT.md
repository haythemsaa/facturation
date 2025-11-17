# 🐳 Docker Deployment Guide - TunisBusiness Suite

Guide complet pour déployer l'application TunisBusiness Suite avec Docker et Docker Compose.

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Configuration](#configuration)
3. [Déploiement Local](#déploiement-local)
4. [Déploiement Production](#déploiement-production)
5. [Commandes Utiles](#commandes-utiles)
6. [Monitoring](#monitoring)
7. [Troubleshooting](#troubleshooting)

---

## 🔧 Prérequis

- **Docker 24+** installé
- **Docker Compose 2.20+** installé
- **2 GB RAM minimum** (4 GB recommandé)
- **10 GB d'espace disque**

### Vérification

```bash
docker --version      # Docker version 24.0+
docker compose version # Docker Compose version v2.20+
```

---

## ⚙️ Configuration

### 1. Copier le fichier d'environnement

```bash
cp .env.example .env
```

### 2. Générer la clé d'application

```bash
docker run --rm -v $(pwd):/app composer:2 \
  sh -c "cd /app && php artisan key:generate"
```

Ou si vous avez PHP localement :

```bash
php artisan key:generate
```

### 3. Configurer les variables d'environnement

Éditez le fichier `.env` :

```env
APP_NAME="TunisBusiness Suite"
APP_ENV=production
APP_KEY=base64:VOTRE_CLE_GENEREE
APP_DEBUG=false
APP_URL=https://votre-domaine.tn

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=facturation
DB_USERNAME=postgres
DB_PASSWORD=CHANGEZ_MOI_PRODUCTION

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=CHANGEZ_MOI_REDIS
REDIS_PORT=6379

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tunisbusiness.tn
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🚀 Déploiement Local

### 1. Build et démarrage des containers

```bash
# Build les images
docker compose build

# Démarrer les services
docker compose up -d
```

### 2. Initialiser la base de données

```bash
# Exécuter les migrations
docker compose exec app php artisan migrate --force

# Peupler avec des données de test (optionnel)
docker compose exec app php artisan db:seed
```

### 3. Optimiser l'application

```bash
# Cache la configuration
docker compose exec app php artisan config:cache

# Cache les routes
docker compose exec app php artisan route:cache

# Cache les vues
docker compose exec app php artisan view:cache
```

### 4. Accéder à l'application

L'application est maintenant accessible sur :

- **API**: http://localhost/api
- **Health Check**: http://localhost/api/health

### 5. Comptes de test

Après le seeding, utilisez ces comptes :

| Email | Mot de passe | Plan | Modules |
|-------|--------------|------|---------|
| admin@starter.tunisbusiness.tn | password | Starter | Stock |
| admin@business.tunisbusiness.tn | password | Business | Stock + CRM |
| admin@enterprise.tunisbusiness.tn | password | Enterprise | Stock + CRM + HR |

---

## 🏭 Déploiement Production

### Architecture

L'application Docker est composée de 5 services :

1. **postgres** - Base de données PostgreSQL 15
2. **redis** - Cache et gestionnaire de queues
3. **app** - Application Laravel (Nginx + PHP-FPM)
4. **queue** - Worker pour traiter les jobs asynchrones
5. **scheduler** - Cron pour les tâches planifiées

### Configuration Production

#### 1. Sécuriser les mots de passe

```bash
# Générer des mots de passe forts
DB_PASSWORD=$(openssl rand -base64 32)
REDIS_PASSWORD=$(openssl rand -base64 32)

# Mettre à jour .env
echo "DB_PASSWORD=$DB_PASSWORD" >> .env
echo "REDIS_PASSWORD=$REDIS_PASSWORD" >> .env
```

#### 2. Configurer SSL/HTTPS (Reverse Proxy)

Utilisez Nginx ou Traefik comme reverse proxy :

```nginx
# /etc/nginx/sites-available/tunisbusiness
server {
    listen 80;
    server_name tunisbusiness.tn www.tunisbusiness.tn;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tunisbusiness.tn www.tunisbusiness.tn;

    ssl_certificate /etc/letsencrypt/live/tunisbusiness.tn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tunisbusiness.tn/privkey.pem;

    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

#### 3. Volumes de données persistantes

Les données sont automatiquement persistées dans des volumes Docker :

- `postgres_data` - Base de données PostgreSQL
- `redis_data` - Cache Redis
- `./storage` - Fichiers uploadés, logs, cache Laravel

#### 4. Backup automatique

Le scheduler exécute automatiquement le backup quotidien à 2h :

```bash
# Vérifier les backups
docker compose exec app ls -lh /var/www/html/storage/app/backups/

# Backup manuel
docker compose exec app php artisan backup:run
```

#### 5. Monitoring des logs

```bash
# Logs de tous les services
docker compose logs -f

# Logs d'un service spécifique
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f postgres

# Logs Laravel
docker compose exec app tail -f storage/logs/laravel.log
```

---

## 🛠️ Commandes Utiles

### Gestion des containers

```bash
# Démarrer tous les services
docker compose up -d

# Arrêter tous les services
docker compose down

# Redémarrer un service
docker compose restart app

# Voir l'état des services
docker compose ps

# Voir les logs
docker compose logs -f app
```

### Gestion de l'application

```bash
# Accéder au shell du container
docker compose exec app sh

# Exécuter une commande Artisan
docker compose exec app php artisan <commande>

# Vider le cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Exécuter les migrations
docker compose exec app php artisan migrate --force

# Créer un utilisateur admin
docker compose exec app php artisan tinker
>>> $tenant = \App\Models\Tenant::first();
>>> $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id, 'email' => 'admin@example.tn']);
>>> $user->assignRole('admin');
```

### Gestion de la base de données

```bash
# Accéder à PostgreSQL
docker compose exec postgres psql -U postgres -d facturation

# Backup manuel de la DB
docker compose exec postgres pg_dump -U postgres facturation > backup.sql

# Restaurer un backup
docker compose exec -T postgres psql -U postgres facturation < backup.sql
```

### Gestion des queues

```bash
# Vérifier le status du queue worker
docker compose logs -f queue

# Redémarrer le queue worker
docker compose restart queue

# Lister les failed jobs
docker compose exec app php artisan queue:failed

# Retry un failed job
docker compose exec app php artisan queue:retry <job-id>

# Retry tous les failed jobs
docker compose exec app php artisan queue:retry all
```

---

## 📊 Monitoring

### Health Checks

Tous les services ont des health checks configurés :

```bash
# Vérifier la santé de l'application
curl http://localhost/api/health

# Vérifier tous les composants
curl http://localhost/api/health/detailed

# Métriques système
curl http://localhost/api/health/metrics
```

### Resource Usage

```bash
# Voir l'utilisation des ressources
docker stats

# Disk usage
docker system df
```

### Scheduler Status

```bash
# Vérifier les tâches planifiées
docker compose exec app php artisan schedule:list

# Tester le scheduler manuellement
docker compose exec scheduler php artisan schedule:run
```

---

## 🔍 Troubleshooting

### L'application ne démarre pas

1. **Vérifier les logs** :
   ```bash
   docker compose logs app
   docker compose logs postgres
   ```

2. **Vérifier la clé d'application** :
   ```bash
   grep APP_KEY .env
   # Doit contenir : APP_KEY=base64:...
   ```

3. **Rebuild les containers** :
   ```bash
   docker compose down -v
   docker compose build --no-cache
   docker compose up -d
   ```

### Erreur de connexion à la base de données

```bash
# Vérifier que PostgreSQL est démarré
docker compose ps postgres

# Vérifier les logs PostgreSQL
docker compose logs postgres

# Tester la connexion
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

### Permission denied sur storage/

```bash
# Fixer les permissions
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/storage
```

### Queue worker ne traite pas les jobs

```bash
# Redémarrer le worker
docker compose restart queue

# Vérifier les logs
docker compose logs -f queue

# Vérifier Redis
docker compose exec redis redis-cli ping
# Doit répondre: PONG
```

### Espace disque plein

```bash
# Nettoyer les images inutilisées
docker system prune -a

# Nettoyer les volumes inutilisés
docker volume prune

# Nettoyer les backups anciens (> 30 jours)
docker compose exec app find /var/www/html/storage/app/backups -mtime +30 -delete
```

---

## 🔄 Mise à jour de l'application

### 1. Pull les dernières modifications

```bash
git pull origin main
```

### 2. Rebuild et redémarrer

```bash
# Rebuild l'image
docker compose build app

# Redémarrer les services
docker compose up -d

# Exécuter les nouvelles migrations
docker compose exec app php artisan migrate --force

# Recacher la configuration
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## 📈 Scaling

### Scaler le queue worker

Pour traiter plus de jobs en parallèle :

```bash
# Augmenter le nombre de workers
docker compose up -d --scale queue=3
```

### Load Balancing

Pour gérer plus de trafic, déployez plusieurs instances de l'app derrière un load balancer :

```yaml
# docker-compose.yml
services:
  app:
    # ... configuration ...
    deploy:
      replicas: 3
```

---

## 🔐 Sécurité

### Best Practices

1. **Ne jamais exposer les ports de la DB et Redis** directement
2. **Utiliser des mots de passe forts** (32+ caractères)
3. **Activer HTTPS** en production
4. **Limiter les origines CORS** dans `.env`
5. **Configurer le firewall** pour n'autoriser que le port 80/443
6. **Scanner les images** régulièrement :
   ```bash
   docker scan tunisbusiness_app
   ```

---

## 📝 Variables d'environnement importantes

| Variable | Description | Défaut |
|----------|-------------|---------|
| `APP_ENV` | Environnement (production/local) | production |
| `APP_DEBUG` | Mode debug (false en prod) | false |
| `DB_PASSWORD` | Mot de passe PostgreSQL | - |
| `REDIS_PASSWORD` | Mot de passe Redis | - |
| `CORS_ALLOWED_ORIGINS` | Origins autorisées pour CORS | * |
| `BACKUP_RETENTION_DAYS` | Rétention backups (jours) | 30 |

---

## 🎉 Félicitations !

Votre application TunisBusiness Suite est maintenant déployée avec Docker !

### Prochaines étapes

1. ✅ Configurer le domaine et SSL
2. ✅ Configurer les emails SMTP
3. ✅ Tester tous les endpoints avec Postman
4. ✅ Configurer les webhooks pour intégrations
5. ✅ Monitorer les logs et performances

### Support

Pour toute question ou problème :
- 📧 Email: support@tunisbusiness.tn
- 📖 Documentation: https://docs.tunisbusiness.tn
- 🐛 Issues: https://github.com/haythemsaa/facturation/issues

---

**Développé avec ❤️ pour les PME tunisiennes**
