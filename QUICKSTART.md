# ⚡ Quick Start - TunisBusiness Suite

Démarrez l'application en **moins de 5 minutes** !

---

## 🚀 Option 1: Docker (Recommandé - Le plus rapide)

### 3 Commandes = Application prête !

```bash
# 1. Copier l'environnement
cp .env.example .env

# 2. Démarrer avec Docker
docker compose up -d

# 3. Initialiser la base de données
docker compose exec app php artisan migrate --seed
```

**C'est tout ! 🎉**

L'application est accessible sur : **http://localhost**

---

## 💻 Option 2: Installation locale avec Makefile

### Utiliser le Makefile (Simple)

```bash
# Installation complète automatique
make setup

# Configuration .env (ajuster vos paramètres DB)
nano .env

# Installation fraîche avec données
make fresh

# Démarrer le serveur
make dev
```

L'application est accessible sur : **http://localhost:8000**

---

## 🛠️ Option 3: Installation manuelle

### Étape par étape

```bash
# 1. Installer les dépendances
composer install

# 2. Configuration
cp .env.example .env
php artisan key:generate

# 3. Configurer la base de données dans .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=facturation
DB_USERNAME=postgres
DB_PASSWORD=votre_mot_de_passe

# 4. Créer la base de données
createdb facturation

# 5. Migrer et peupler
php artisan migrate --seed

# 6. Lancer le serveur
php artisan serve
```

---

## 🔐 Comptes de Test

Après le seeding, utilisez ces comptes :

| Compte | Email | Mot de passe | Modules |
|--------|-------|--------------|---------|
| **Starter** | admin@starter.tunisbusiness.tn | password | Stock |
| **Business** | admin@business.tunisbusiness.tn | password | Stock + CRM |
| **Enterprise** | admin@enterprise.tunisbusiness.tn | password | Stock + CRM + HR |

---

## 🧪 Tester l'API

### 1. Via Postman

```bash
# Importer la collection
# Fichier: postman_collection.json
```

1. Ouvrir Postman
2. Import → Upload Files → `postman_collection.json`
3. Configurer les variables :
   - `base_url`: http://localhost:8000 (ou http://localhost si Docker)
4. Lancer "Login" pour obtenir le token automatiquement
5. Toutes les autres requêtes utiliseront le token

### 2. Via curl

```bash
# Health check (public)
curl http://localhost/api/health

# Login
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@enterprise.tunisbusiness.tn","password":"password"}'

# Utiliser le token reçu
export TOKEN="votre_token_ici"

# Obtenir les notifications
curl http://localhost/api/notifications \
  -H "Authorization: Bearer $TOKEN"

# Statistiques
curl http://localhost/api/dashboard/stats \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📊 Vérifier que tout fonctionne

### Health Checks

```bash
# Basic health
curl http://localhost/api/health

# Detailed health (avec Docker)
curl http://localhost/api/health/detailed

# Ou avec Makefile
make health
make health-detailed
```

Réponse attendue :
```json
{
  "status": "healthy",
  "timestamp": "2025-11-17T10:30:00Z",
  "version": "1.0.0"
}
```

---

## 🎯 Commandes Utiles

### Avec Makefile (Recommandé)

```bash
make help              # Voir toutes les commandes disponibles
make dev               # Démarrer serveur
make test              # Lancer les tests
make fresh             # Reset DB avec données fraîches
make docker-up         # Démarrer Docker
make docker-logs       # Voir les logs Docker
make backup            # Faire un backup
make optimize          # Optimiser pour production
```

### Docker

```bash
# Logs en temps réel
docker compose logs -f app

# Shell dans le container
docker compose exec app sh

# Artisan dans Docker
docker compose exec app php artisan route:list

# Redémarrer
docker compose restart app
```

### Laravel Artisan

```bash
# Lister les routes
php artisan route:list

# Vider les caches
php artisan cache:clear

# Voir les migrations
php artisan migrate:status

# Créer un utilisateur en tinker
php artisan tinker
>>> $user = User::first()
>>> $user->assignRole('admin')
```

---

## 🗂️ Structure des Données Créées

Après `db:seed`, vous aurez :

### ✅ 3 Tenants
- Starter Company 1
- Business Company 2
- Enterprise Company 3

### ✅ 15 Utilisateurs
- 5 par tenant (1 admin + 4 users)

### ✅ Stock & Facturation
- 50 produits avec catégories
- 30 clients
- 15 fournisseurs
- 3 entrepôts avec stock
- Documents de vente (factures, devis)

### ✅ CRM (Business & Enterprise)
- Pipeline de vente
- 40 contacts
- 20 opportunités
- Activités commerciales

### ✅ RH & Paie (Enterprise uniquement)
- 5 départements
- 8 postes
- 20 employés avec contrats

### ✅ Fonctionnalités Pro
- 6-9 webhooks (2-3 par tenant)
- 75-150 notifications (5-10 par user, mix lu/non lu)
- Templates de documents (3 par tenant)
- Paramètres tenant (30+ settings)

---

## 📝 Prochaines Étapes

### 1. Explorer l'API

```bash
# Avec Postman (recommandé)
# Ouvrir postman_collection.json

# Ou consulter la doc
cat API_DOCUMENTATION.md
```

### 2. Lancer les Tests

```bash
# Tous les tests (24 tests)
make test

# Tests spécifiques
make test-filter TEST=HealthCheckTest
make test-filter TEST=NotificationTest
make test-filter TEST=AuthenticationTest
make test-filter TEST=ExportTest
```

### 3. Consulter la Documentation

| Guide | Contenu |
|-------|---------|
| [README.md](README.md) | Overview complet |
| [GETTING_STARTED.md](GETTING_STARTED.md) | Guide détaillé installation |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | 190+ endpoints API |
| [DOCKER_DEPLOYMENT.md](DOCKER_DEPLOYMENT.md) | Guide Docker production |
| [CHANGELOG.md](CHANGELOG.md) | Historique des versions |

### 4. Développer votre Frontend

L'API est complète et prête ! Vous pouvez maintenant :
- Créer un dashboard Vue.js / React
- Utiliser les 190+ endpoints disponibles
- Tous les modules sont fonctionnels

---

## 🔥 Déploiement Production

### Script automatique

```bash
./deploy.sh production
```

Ou avec Makefile :

```bash
make deploy-production
```

### Checklist Production

- [ ] Configurer `.env` (APP_ENV=production, APP_DEBUG=false)
- [ ] Configurer base de données PostgreSQL
- [ ] Configurer Redis pour cache/queue
- [ ] Lancer migrations : `php artisan migrate --force`
- [ ] Optimiser : `make optimize`
- [ ] Configurer cron pour scheduler
- [ ] Configurer worker pour queues
- [ ] Tester health checks : `/api/health/detailed`

---

## ❓ Problèmes Courants

### Permission denied sur storage/

```bash
chmod -R 775 storage bootstrap/cache
# Ou avec Makefile
make permissions
```

### Docker containers ne démarrent pas

```bash
docker compose down -v
docker compose up -d
```

### Tests échouent

```bash
# Reset complet
make fresh
make test
```

### 404 sur les routes API

```bash
# Vider les caches
make clear
```

---

## 🆘 Aide

### Commandes de Debug

```bash
# Voir les routes
php artisan route:list | grep api

# Voir les logs
tail -f storage/logs/laravel.log

# Avec Docker
docker compose logs -f app

# Vérifier la config
php artisan about
```

### Support

- 📖 **Documentation** : Voir les guides dans le projet
- 🐛 **Issues** : https://github.com/haythemsaa/facturation/issues
- 💬 **Email** : support@tunisbusiness.tn

---

## 🎉 Félicitations !

Votre application **TunisBusiness Suite** est maintenant opérationnelle !

**Temps de démarrage : < 5 minutes** ✅

### Ce qui est prêt :

✅ Backend complet (Laravel 11)
✅ API REST (190+ endpoints)
✅ 3 Modules métier (Stock, CRM, HR)
✅ Fonctionnalités Pro (Notifications, Audit, Exports, etc.)
✅ Docker production-ready
✅ 24 tests automatisés
✅ Données de démo
✅ Documentation complète

### Prochaine étape : **Développer votre frontend !** 🚀

---

**Fait avec ❤️ en Tunisie 🇹🇳**
