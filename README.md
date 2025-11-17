# TunisBusiness Suite - Plateforme SaaS pour PME Tunisiennes

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-purple.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

Plateforme SaaS modulaire tout-en-un destinée aux PME tunisiennes avec trois modules principaux : **Stock & Facturation**, **CRM**, et **RH & Paie**.

## Table des Matières

- [Aperçu](#aperçu)
- [Fonctionnalités](#fonctionnalités)
- [Stack Technique](#stack-technique)
- [Architecture](#architecture)
- [Installation](#installation)
- [Conformité Légale](#conformité-légale)
- [Modules](#modules)
- [Déploiement](#déploiement)

## Aperçu

**TunisBusiness Suite** est une solution SaaS moderne conçue spécifiquement pour les PME tunisiennes, offrant une gestion complète et conforme aux normes locales.

### Avantages Clés

- ✅ **Prix accessible** : 70-85% moins cher que la concurrence internationale
- ✅ **Conformité totale** : 100% conforme à la réglementation tunisienne
- ✅ **Multi-tenant** : Architecture SaaS scalable et sécurisée
- ✅ **Interface moderne** : Vue.js 3 avec Tailwind CSS
- ✅ **Support local** : Équipe tunisienne bilingue FR/AR

## Fonctionnalités

### 📦 Module Stock & Facturation

- Gestion complète des articles et stocks multi-dépôts
- Facturation conforme (Devis, BL, Factures, Avoirs)
- Gestion clients/fournisseurs
- Calculs TVA conformes (19%, 13%, 7%, 0%)
- Timbre fiscal automatique (1% plafonné à 1 TND)
- Traçabilité complète des mouvements
- Inventaires et rapports de valorisation

### 👥 Module CRM

- Gestion contacts et leads avec qualification
- Pipeline commercial visuel (drag & drop)
- Activités commerciales (appels, emails, rendez-vous)
- Tournées commerciales avec géolocalisation
- Check-in/Check-out GPS pour visites terrain
- Objectifs et rapports de performance

### 💼 Module RH & Paie

- Dossiers employés complets
- Gestion des contrats (CDI, CDD, SIVP, Karama)
- Pointage multi-mode et gestion des absences
- Calcul de paie conforme CNSS/IRPP
- Cotisations sociales automatiques (9.18% salarié, 16.57% employeur)
- Barème IRPP 2025 avec déductions
- Déclarations sociales automatisées

## Stack Technique

### Backend

- **Framework** : Laravel 11 (PHP 8.2+)
- **Base de données** : PostgreSQL 15+
- **Cache & Queues** : Redis 7+
- **Authentication** : Laravel Sanctum
- **Permissions** : Spatie Laravel Permission
- **Architecture** : Multi-tenant strict

### Frontend (À implémenter)

- **Framework** : Vue.js 3 ou React 18+
- **Styling** : Tailwind CSS 3
- **Build Tool** : Vite 5

## Architecture

### Structure de la Base de Données

L'application utilise une architecture multi-tenant stricte avec isolation complète des données par `tenant_id`.

#### Tables Principales

**Multi-tenant & Auth**
- `tenants` - Entreprises clientes
- `subscriptions` - Abonnements et plans
- `users` - Utilisateurs avec rôles

**Module Stock & Facturation**
- `categories`, `products` - Articles
- `warehouses`, `stocks`, `stock_movements` - Stocks
- `customers`, `suppliers` - Tiers
- `documents`, `document_lines` - Documents commerciaux
- `payments` - Paiements
- `inventories` - Inventaires

**Module CRM**
- `contacts` - Contacts/Leads
- `pipelines`, `pipeline_stages`, `opportunities` - Pipeline commercial
- `activities` - Activités
- `tours`, `tour_visits` - Tournées
- `sales_targets` - Objectifs

**Module RH & Paie**
- `departments`, `positions`, `employees` - Organisation
- `contracts`, `attendances` - Contrats et pointage
- `leave_types`, `leave_requests`, `leave_balances` - Congés
- `payroll_items`, `payslips`, `payslip_lines` - Paie
- `cnss_declarations`, `irpp_declarations` - Déclarations sociales

### Services de Conformité

#### TaxCalculator (`app/Services/Compliance/TaxCalculator.php`)

Service de calcul des taxes tunisiennes :

```php
// Calcul TVA
$tva = $taxCalculator->calculateTVA($amountHT, '19');

// Calcul timbre fiscal (1% max 1 TND)
$timbre = $taxCalculator->calculateTimbreFiscal($amountTTC);

// Calcul complet d'un document
$totals = $taxCalculator->calculateDocument($lines, $discountRate);
```

#### PayrollCalculator (`app/Services/Compliance/PayrollCalculator.php`)

Service de calcul de paie conforme :

```php
// Calcul bulletin de paie complet
$payslip = $payrollCalculator->calculatePayslip([
    'base_salary' => 1500.000,
    'is_family_head' => true,
    'children_count' => 2,
    'worked_days' => 26,
]);

// Génération déclaration CNSS
$cnssDeclaration = $payrollCalculator->generateCNSSDeclaration($payslips);
```

## Installation

### Prérequis

- PHP >= 8.2
- Composer
- PostgreSQL >= 15
- Redis >= 7
- Node.js >= 18 (pour le frontend)

### Étapes d'Installation

1. **Cloner le repository**

```bash
git clone https://github.com/haythemsaa/facturation.git
cd facturation
```

2. **Installer les dépendances PHP**

```bash
composer install
```

3. **Configuration de l'environnement**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**

Éditer `.env` :

```env
APP_NAME="TunisBusiness Suite"
APP_TIMEZONE=Africa/Tunis
APP_LOCALE=fr

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tunisbusiness
DB_USERNAME=postgres
DB_PASSWORD=votre_mot_de_passe
```

5. **Créer la base de données**

```bash
createdb tunisbusiness
```

6. **Exécuter les migrations**

```bash
php artisan migrate
```

7. **Lancer le serveur de développement**

```bash
php artisan serve
```

L'application sera accessible sur `http://localhost:8000`

## Conformité Légale

### Réglementation Tunisienne

#### TVA (Taxe sur la Valeur Ajoutée)

- **19%** - Taux normal
- **13%** - Taux réduit
- **7%** - Taux super réduit
- **0%** - Exonéré

#### Timbre Fiscal

- **1%** du montant TTC
- **Plafonné à 1.000 TND**

#### CNSS (Caisse Nationale de Sécurité Sociale)

**Cotisations salariales** : 9.18%
**Cotisations patronales** : 16.57%
**Autres contributions** :
- **CSS** : 1% (Contribution Sociale de Solidarité)
- **TFP** : 1% (Taxe de Formation Professionnelle)
- **FOPROLOS** : 1% (Fonds de Promotion du Logement Social)

#### IRPP (Impôt sur le Revenu des Personnes Physiques)

**Barème 2025** (revenu annuel) :
- 0 - 5 000 TND : 0%
- 5 000 - 20 000 TND : 26%
- 20 000 - 30 000 TND : 28%
- 30 000 - 50 000 TND : 32%
- Au-delà de 50 000 TND : 35%

**Déductions** :
- Chef de famille : 300 TND/an
- Par enfant à charge : 100 TND/an (max 4 enfants)

#### Code du Travail

- **Durée légale** : 40h/semaine, 8h/jour
- **Congés annuels** : 1 jour/mois (12 jours/an minimum)
- **Heures supplémentaires** : +50% (jours normaux), +75% (repos), +100% (fériés)

## Modules

### Module Stock & Facturation

#### Créer un Article

```php
$product = Product::create([
    'tenant_id' => auth()->user()->tenant_id,
    'code' => 'ART-001',
    'name' => 'Article Test',
    'selling_price' => 100.000,
    'tva_rate' => '19',
]);
```

#### Créer une Facture

```php
use App\Services\Compliance\TaxCalculator;

$calculator = new TaxCalculator();

$document = Document::create([
    'tenant_id' => auth()->user()->tenant_id,
    'type' => 'invoice',
    'number' => 'FAC-2025-001',
    'customer_id' => $customer->id,
    'date' => now(),
]);

$totals = $calculator->calculateDocument($lines);
$document->update($totals);
```

### Module CRM

#### Créer un Contact/Lead

```php
$contact = Contact::create([
    'tenant_id' => auth()->user()->tenant_id,
    'first_name' => 'Ahmed',
    'last_name' => 'Ben Ali',
    'type' => 'lead',
    'source' => 'website',
    'assigned_to' => $commercial->id,
]);
```

### Module RH & Paie

#### Créer un Employé

```php
$employee = Employee::create([
    'tenant_id' => auth()->user()->tenant_id,
    'employee_number' => 'EMP-001',
    'first_name' => 'Mohamed',
    'last_name' => 'Trabelsi',
    'cin' => '12345678',
    'hire_date' => now(),
    'is_family_head' => true,
    'children_count' => 2,
]);
```

#### Calculer un Bulletin de Paie

```php
use App\Services\Compliance\PayrollCalculator;

$calculator = new PayrollCalculator();

$payslip = $calculator->calculatePayslip([
    'base_salary' => $employee->contract->base_salary,
    'is_family_head' => $employee->is_family_head,
    'children_count' => $employee->children_count,
    'worked_days' => 26,
    'earnings' => [
        'prime_transport' => 50.000,
    ],
]);

Payslip::create([
    'tenant_id' => $employee->tenant_id,
    'employee_id' => $employee->id,
    'month' => now()->month,
    'year' => now()->year,
    ...$payslip,
]);
```

## Déploiement

### Production avec Docker

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan optimize
```

### Optimisations Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Roadmap

### Phase 1 : MVP (Mois 1-4) ✅
- [x] Architecture multi-tenant
- [x] Module Stock & Facturation
- [x] Module CRM
- [x] Module RH & Paie
- [x] Calculs conformes

### Phase 2 : Interface Frontend (Mois 5-6)
- [ ] Dashboard Vue.js 3
- [ ] Interfaces des 3 modules
- [ ] Rapports et analytics

### Phase 3 : Mobile & Optimisations (Mois 7-9)
- [ ] Application mobile Flutter
- [ ] Optimisations performance
- [ ] Intégrations

## License

Ce projet est sous licence MIT.

## Support

- **Email** : support@tunisbusiness.tn
- **Issues** : https://github.com/haythemsaa/facturation/issues

---

**Fait avec ❤️ en Tunisie 🇹🇳**
