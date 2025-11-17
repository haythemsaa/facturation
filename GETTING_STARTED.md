# 🚀 Guide de Démarrage - TunisBusiness Suite

Guide complet pour installer et démarrer l'application SaaS de gestion pour PME tunisiennes.

---

## 📋 Table des Matières

1. [Prérequis](#prérequis)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Base de Données](#base-de-données)
5. [Démarrage](#démarrage)
6. [Tests](#tests)
7. [Fonctionnalités Disponibles](#fonctionnalités-disponibles)
8. [API Documentation](#api-documentation)

---

## 🔧 Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- **PHP 8.2+** avec extensions :
  - BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD
- **Composer 2.x**
- **PostgreSQL 15+** (recommandé pour production)
- **Node.js 18+** & npm (optionnel, pour front-end)
- **Redis** (optionnel, pour cache et queues)

### Vérification des prérequis

```bash
php -v                    # PHP 8.2+
composer --version        # Composer 2.x
psql --version           # PostgreSQL 15+
```

---

## 📦 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/haythemsaa/facturation.git
cd facturation
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript (optionnel)

```bash
npm install
npm run build
```

---

## ⚙️ Configuration

### 1. Copier le fichier d'environnement

```bash
cp .env.example .env
```

### 2. Générer la clé d'application

```bash
php artisan key:generate
```

### 3. Configurer la base de données

Éditez le fichier `.env` avec vos informations PostgreSQL :

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=facturation
DB_USERNAME=postgres
DB_PASSWORD=votre_mot_de_passe
```

### 4. Configuration Tunisie

Les paramètres sont déjà configurés pour la Tunisie dans `.env` :

```env
APP_TIMEZONE=Africa/Tunis
APP_LOCALE=fr
APP_SUPPORTED_LOCALES=fr,ar,en

# Tunisian Fiscal Settings
DEFAULT_TVA_RATE=19.00
APPLY_TIMBRE_FISCAL=true
MAX_TIMBRE_FISCAL=1.00

# HR & Payroll
CNSS_RATE_EMPLOYEE=9.18
CNSS_RATE_EMPLOYER=16.57
```

---

## 🗄️ Base de Données

### 1. Créer la base de données

```bash
# Se connecter à PostgreSQL
psql -U postgres

# Créer la base de données
CREATE DATABASE facturation;

# Quitter
\q
```

### 2. Exécuter les migrations

```bash
php artisan migrate
```

Cette commande va créer toutes les tables nécessaires :
- Tenants (multi-tenant)
- Users & Permissions (Spatie)
- Stock & Facturation (Products, Customers, Documents, etc.)
- CRM (Contacts, Opportunities, Pipelines, Activities)
- HR & Paie (Employees, Contracts, Payslips, CNSS/IRPP)
- Notifications (in-app notifications)
- Audit Logs (traçabilité)
- Webhooks (intégrations)
- Document Templates (templates PDF personnalisables)
- Translations (multi-langue)

### 3. Peupler avec des données de test (optionnel)

```bash
php artisan db:seed
```

Cela va créer :
- ✅ 3 tenants (Starter, Business, Enterprise)
- ✅ Rôles et permissions
- ✅ 5 utilisateurs par tenant
- ✅ 50 produits avec stock
- ✅ 30 clients et 15 fournisseurs
- ✅ Documents de vente (factures, devis)
- ✅ Pipeline CRM avec opportunités
- ✅ 20 employés avec contrats
- ✅ Paramètres tenant par défaut
- ✅ Templates de documents professionnels

**Comptes de test créés :**

| Email | Mot de passe | Rôle | Plan |
|-------|--------------|------|------|
| admin@starter.tunisbusiness.tn | password | Admin | Starter (Stock) |
| admin@business.tunisbusiness.tn | password | Admin | Business (Stock + CRM) |
| admin@enterprise.tunisbusiness.tn | password | Admin | Enterprise (Stock + CRM + HR) |

---

## 🚀 Démarrage

### 1. Démarrer le serveur de développement

```bash
php artisan serve
```

L'application sera accessible sur : **http://localhost:8000**

### 2. Démarrer le worker de queues (optionnel)

Pour traiter les jobs en arrière-plan (emails, notifications, webhooks) :

```bash
php artisan queue:work
```

### 3. Démarrer le scheduler (production)

Pour les tâches planifiées (backups, nettoyage, etc.), ajoutez à votre crontab :

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Tests

### Exécuter tous les tests

```bash
php artisan test
```

### Exécuter des tests spécifiques

```bash
# Tests de santé (Health Checks)
php artisan test --filter HealthCheckTest

# Tests de notifications
php artisan test --filter NotificationTest
```

### Tests disponibles

- ✅ **HealthCheckTest** : 3 tests (basic, detailed, metrics)
- ✅ **NotificationTest** : 6 tests (CRUD, read/unread, permissions)

---

## 🎯 Fonctionnalités Disponibles

### 📦 Module STOCK & FACTURATION

- ✅ Gestion produits & catégories
- ✅ Multi-entrepôts avec mouvements de stock
- ✅ Gestion clients & fournisseurs
- ✅ Documents commerciaux (Devis, BL, Factures, Avoirs)
- ✅ Gestion des paiements
- ✅ Inventaires physiques
- ✅ Rapports (valorisation stock, ventes, achats, TVA)
- ✅ **Conformité fiscale Tunisie** (TVA 19%/13%/7%/0%, Timbre Fiscal)

### 👥 Module CRM

- ✅ Contacts & Leads avec qualification
- ✅ Pipelines de vente personnalisables
- ✅ Opportunités avec étapes
- ✅ Activités (rendez-vous, appels, tâches)
- ✅ Tournées commerciales avec géolocalisation
- ✅ Objectifs de vente
- ✅ Tags & segmentation
- ✅ Rapports de conversion

### 💼 Module RH & PAIE

- ✅ Gestion employés & départements
- ✅ Contrats de travail avec avenants
- ✅ Pointage (check-in/out)
- ✅ Gestion des congés
- ✅ Calcul bulletins de paie
- ✅ **Déclarations CNSS** (9.18% / 16.57%)
- ✅ **Déclarations IRPP 2025** (barème progressif)
- ✅ Documents RH

### 🔔 Fonctionnalités Professionnelles

#### Notifications In-App
- ✅ Notifications temps réel
- ✅ 4 niveaux de priorité (low/normal/high/urgent)
- ✅ Filtrage par type et statut
- ✅ Compteur non lues
- ✅ Actions contextuelles (deep linking)

#### Audit Logs
- ✅ Traçabilité complète (created/updated/deleted)
- ✅ Capture old/new values
- ✅ IP address & user agent
- ✅ Filtrage par utilisateur, modèle, date
- ✅ Export Excel pour conformité

#### Webhooks
- ✅ Subscription aux événements système
- ✅ HMAC-SHA256 signatures
- ✅ Retry logic (3 tentatives)
- ✅ Logs des appels (success/failure)

#### Rate Limiting
- ✅ 4 tiers : strict (10/min), default (60/min), relaxed (200/min), unlimited (10k/min)
- ✅ Par user+tenant ou par IP
- ✅ Headers : X-RateLimit-Limit, X-RateLimit-Remaining

#### Templates de Documents
- ✅ Templates HTML personnalisables
- ✅ Variables dynamiques ({{document.number}}, {{customer.name}}, etc.)
- ✅ 3 templates par défaut (Facture, Devis, BL)
- ✅ Styles CSS par tenant

#### Exports PDF & Excel
- ✅ **PDF** : DomPDF (download/stream/save, bulk export)
- ✅ **Excel** : PhpSpreadsheet avec 7 types de rapports
  - Ventes, Stock, Contacts, Opportunités, Employés, Audit Logs
- ✅ Styling professionnel (headers colorés, totaux, conditional formatting)

#### Multi-langue
- ✅ Français, Arabe, English
- ✅ 100+ traductions par langue
- ✅ Détection automatique (URL → header → user → tenant → default)
- ✅ Support RTL pour Arabe

#### Health Monitoring
- ✅ Basic : status + timestamp
- ✅ Detailed : DB, Cache, Storage, Queue checks
- ✅ Metrics : tenants, users, documents, database/storage size

#### Backup Automation
- ✅ Commande `php artisan backup:run`
- ✅ PostgreSQL pg_dump avec compression
- ✅ Backup storage (tar.gz)
- ✅ Nettoyage automatique (30 jours)

---

## 📚 API Documentation

### Endpoints Principaux

**Health & Monitoring (Public)**
- `GET /api/health` - Basic health check
- `GET /api/health/detailed` - Detailed component checks
- `GET /api/health/metrics` - System metrics

**Authentification**
- `POST /api/register` - Créer compte tenant
- `POST /api/login` - Connexion (Bearer token)
- `POST /api/logout` - Déconnexion
- `GET /api/me` - Profil utilisateur

**Notifications** (Auth required)
- `GET /api/notifications` - Liste notifications
- `GET /api/notifications/unread-count` - Compteur non lues
- `POST /api/notifications/{id}/read` - Marquer comme lu
- `POST /api/notifications/mark-all-read` - Tout marquer comme lu

**Audit Logs** (Admin only)
- `GET /api/audit-logs` - Liste logs
- `GET /api/audit-logs/{id}` - Détails log

**Exports**
- `GET /api/export/document/{id}/pdf?template_id=X` - Export PDF
- `POST /api/export/bulk-pdf` - Export PDF multiple
- `GET /api/export/sales/excel?start_date=X&end_date=Y` - Rapport ventes Excel
- `GET /api/export/stock/excel` - Rapport stock Excel
- `GET /api/export/contacts/excel` - Contacts Excel
- `GET /api/export/opportunities/excel` - Opportunités Excel
- `GET /api/export/employees/excel` - Employés Excel
- `GET /api/export/audit-logs/excel` - Audit logs Excel

**Stock & Facturation** (`/api/stock/*`)
- Products, Categories, Warehouses
- Stock movements, Transfers, Adjustments
- Customers, Suppliers
- Documents (Invoices, Quotes, Delivery Notes)
- Payments, Inventories
- Reports (TVA, Sales, Purchases)

**CRM** (`/api/crm/*`)
- Contacts, Pipelines, Opportunities
- Activities, Tours (tournées commerciales)
- Sales Targets, Tags
- Reports (conversion, pipeline)

**HR & Paie** (`/api/hr/*`)
- Departments, Positions, Employees
- Contracts, Attendances, Leave Requests
- Payslips, CNSS/IRPP Declarations
- HR Documents, Reports

### Documentation Complète

Consultez `API_DOCUMENTATION.md` pour la documentation complète avec exemples de requêtes/réponses.

---

## 🔐 Sécurité

### Best Practices Implémentées

- ✅ **Laravel Sanctum** pour authentification API
- ✅ **Spatie Permissions** pour RBAC (Role-Based Access Control)
- ✅ **Multi-tenant isolation** via BelongsToTenant trait
- ✅ **Rate Limiting** sur toutes routes API
- ✅ **Audit Logs** pour traçabilité
- ✅ **CSRF Protection** sur routes web
- ✅ **SQL Injection** protection via Eloquent ORM
- ✅ **XSS Protection** via Blade templating
- ✅ **Password Hashing** avec bcrypt (rounds: 12)
- ✅ **API Throttling** (4 tiers configurables)

### Configuration Sécurité

Dans `.env` :

```env
PASSWORD_EXPIRES_DAYS=90      # Expiration mot de passe
SESSION_TIMEOUT_MINUTES=120   # Timeout session
ENABLE_2FA=false              # Activer 2FA (à implémenter)
```

---

## 🛠️ Commandes Utiles

### Backup

```bash
# Backup complet (DB + storage)
php artisan backup:run

# Backup database uniquement
php artisan backup:run --database

# Backup storage uniquement
php artisan backup:run --storage
```

### Maintenance

```bash
# Vider cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimisation production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Réindexer
php artisan optimize
```

### Database

```bash
# Reset et seed
php artisan migrate:fresh --seed

# Rollback migration
php artisan migrate:rollback

# Status migrations
php artisan migrate:status
```

---

## 📈 Statistiques du Projet

| Métrique | Valeur |
|----------|--------|
| **Lignes de code** | ~7,250 lignes |
| **Contrôleurs** | 35+ contrôleurs API |
| **Models** | 40+ models Eloquent |
| **Migrations** | 25+ migrations |
| **Routes API** | 190+ endpoints |
| **Services** | 10+ services métier |
| **Events/Listeners** | 12 events, 15 listeners |
| **Middlewares** | 6 middlewares custom |
| **Tests** | 9 feature tests |
| **Langues** | 3 (FR, AR, EN) |
| **Compétitivité** | 98% vs Odoo/Zoho/Sage |

---

## 🇹🇳 Conformité Tunisie

### Fiscalité

- ✅ **TVA** : 19%, 13%, 7%, 0% (configurable)
- ✅ **Timbre Fiscal** : 1% (max 1 TND)
- ✅ **Numéro Matricule Fiscal** sur documents
- ✅ **Format factures** conforme législation tunisienne

### Paie & Social

- ✅ **CNSS** : 9.18% employé / 16.57% employeur
- ✅ **IRPP 2025** : Barème progressif (0% à 35%)
- ✅ **Congés payés** : 12 jours/an (configurable)
- ✅ **Déclarations trimestrielles** CNSS
- ✅ **Déclaration mensuelle** IRPP

### Timezone & Formats

- ✅ **Timezone** : Africa/Tunis
- ✅ **Monnaie** : TND (Dinar Tunisien)
- ✅ **Format dates** : DD/MM/YYYY
- ✅ **Langue principale** : Français
- ✅ **Support Arabe** : RTL ready

---

## 🆘 Support & Aide

### Problèmes Courants

**Erreur : "Connection refused" PostgreSQL**
```bash
# Vérifier que PostgreSQL est démarré
sudo systemctl status postgresql
sudo systemctl start postgresql
```

**Erreur : "Class not found"**
```bash
# Regénérer autoload
composer dump-autoload
```

**Erreur : "SQLSTATE[HY000] [2002]"**
```bash
# Vérifier connexion DB dans .env
# Vérifier que la DB existe
psql -U postgres -l
```

**Permission denied storage/**
```bash
# Donner permissions
chmod -R 775 storage bootstrap/cache
```

### Logs

Les logs se trouvent dans `storage/logs/laravel.log`

```bash
# Voir les derniers logs
tail -f storage/logs/laravel.log
```

---

## 🎉 Félicitations !

Votre application **TunisBusiness Suite** est maintenant prête à l'emploi !

### Prochaines Étapes

1. ✅ Tester les fonctionnalités via l'API (Postman, Insomnia)
2. ✅ Personnaliser les templates de documents
3. ✅ Configurer les webhooks pour intégrations
4. ✅ Activer le backup automatique (cron)
5. ✅ Déployer en production (voir DEPLOYMENT.md)

### Ressources

- 📖 **Documentation API** : `API_DOCUMENTATION.md`
- 🏆 **Analyse Compétitive** : `COMPETITIVE_ANALYSIS.md`
- 🚀 **Déploiement** : `DEPLOYMENT.md` (à créer)

---

## 📝 Licence

Ce projet est sous licence propriétaire. Tous droits réservés © 2025 TunisBusiness Suite.

---

**Développé avec ❤️ pour les PME tunisiennes**
