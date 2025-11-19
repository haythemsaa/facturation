# 🤝 Guide de Contribution - TunisBusiness Suite

Merci de votre intérêt pour contribuer à TunisBusiness Suite ! Ce guide vous aidera à contribuer efficacement au projet.

## 📋 Table des Matières

1. [Code de Conduite](#code-de-conduite)
2. [Comment Contribuer](#comment-contribuer)
3. [Setup Développement](#setup-développement)
4. [Standards de Code](#standards-de-code)
5. [Processus de Pull Request](#processus-de-pull-request)
6. [Rapporter des Bugs](#rapporter-des-bugs)
7. [Proposer des Fonctionnalités](#proposer-des-fonctionnalités)
8. [Tests](#tests)
9. [Documentation](#documentation)

---

## Code de Conduite

En participant à ce projet, vous acceptez de respecter notre [Code de Conduite](CODE_OF_CONDUCT.md). Soyez respectueux, constructif et professionnel dans toutes vos interactions.

### Principes

- 🤝 **Respect** : Traitez tous les contributeurs avec respect
- 💡 **Constructivité** : Apportez des critiques constructives
- 🎯 **Focus** : Restez concentré sur l'objectif du projet
- 🌍 **Inclusivité** : Accueillez les contributeurs de tous horizons

---

## Comment Contribuer

Il existe plusieurs façons de contribuer :

### 1. Signaler des Bugs 🐛
Utilisez les [Issues GitHub](https://github.com/haythemsaa/facturation/issues) avec le label `bug`.

### 2. Proposer des Fonctionnalités ✨
Créez une issue avec le label `enhancement` pour discuter de votre idée.

### 3. Améliorer la Documentation 📝
La documentation est aussi importante que le code !

### 4. Soumettre du Code 💻
Suivez le processus de Pull Request décrit ci-dessous.

### 5. Répondre aux Issues 💬
Aidez d'autres utilisateurs en répondant à leurs questions.

---

## Setup Développement

### Prérequis

- PHP 8.2+
- Composer 2.5+
- PostgreSQL 15+ ou Docker
- Redis 7+ ou Docker
- Node.js 18+ (pour assets frontend si applicable)
- Git 2.30+

### Installation Locale

```bash
# 1. Forker le repository sur GitHub
# 2. Cloner votre fork
git clone https://github.com/VOTRE_USERNAME/facturation.git
cd facturation

# 3. Ajouter l'upstream
git remote add upstream https://github.com/haythemsaa/facturation.git

# 4. Installation
composer install
cp .env.example .env
php artisan key:generate

# 5. Configuration database
# Modifier .env avec vos credentials DB

# 6. Migrations + seeds
php artisan migrate --seed

# 7. Démarrer serveur
php artisan serve
```

### Installation Docker

```bash
# 1. Cloner
git clone https://github.com/VOTRE_USERNAME/facturation.git
cd facturation

# 2. Démarrer
make docker-up
make setup

# 3. Accéder
# http://localhost:8000
```

---

## Standards de Code

### Style de Code

Nous suivons **PSR-12** pour le PHP.

```bash
# Vérifier le style
composer run phpcs

# Auto-fix
composer run phpcbf
```

### Conventions de Nommage

#### Classes

```php
// PascalCase pour classes
class InvoiceController extends Controller
{
    // camelCase pour méthodes
    public function createInvoice(): JsonResponse
    {
        // snake_case pour variables
        $invoice_number = $this->generateNumber();
    }
}
```

#### Base de Données

```php
// snake_case pour tables et colonnes
Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id');
    $table->decimal('unit_price', 12, 3);
});
```

#### Routes

```php
// kebab-case pour URLs
Route::get('/api/invoice-items', [InvoiceItemController::class, 'index']);
```

### Commentaires & Documentation

```php
/**
 * Create a new invoice for the tenant.
 *
 * @param CreateInvoiceRequest $request The validated request
 * @return JsonResponse The created invoice
 * @throws \Exception If invoice creation fails
 */
public function store(CreateInvoiceRequest $request): JsonResponse
{
    // Logique claire et commentée si nécessaire
}
```

### Type Hints

Utilisez toujours les type hints (PHP 8.2+).

```php
// ✅ Bon
public function calculateTotal(float $amount, float $tvaRate): float
{
    return round($amount * (1 + $tvaRate / 100), 3);
}

// ❌ Mauvais
public function calculateTotal($amount, $tvaRate)
{
    return round($amount * (1 + $tvaRate / 100), 3);
}
```

---

## Processus de Pull Request

### 1. Créer une Branche

```bash
# Synchroniser avec upstream
git checkout main
git pull upstream main

# Créer branche feature
git checkout -b feature/add-payment-gateway

# Ou branche fix
git checkout -b fix/invoice-calculation-bug
```

### 2. Développer

```bash
# Faire vos modifications
# Commiter régulièrement
git add .
git commit -m "feat: Add Stripe payment gateway integration"

# Suivre Conventional Commits:
# feat: Nouvelle fonctionnalité
# fix: Correction de bug
# docs: Documentation
# style: Formatage
# refactor: Refactorisation
# test: Ajout tests
# chore: Maintenance
```

### 3. Tests

```bash
# Lancer tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=InvoiceTest

# Coverage
php artisan test --coverage
```

### 4. Push

```bash
# Push vers votre fork
git push origin feature/add-payment-gateway
```

### 5. Créer Pull Request

1. Aller sur GitHub (votre fork)
2. Cliquer "New Pull Request"
3. Choisir base: `main` ← compare: `feature/add-payment-gateway`
4. Remplir le template PR
5. Soumettre

### Template Pull Request

```markdown
## Description
Brève description des changements.

## Type de changement
- [ ] Bug fix
- [ ] Nouvelle fonctionnalité
- [ ] Breaking change
- [ ] Documentation

## Tests
- [ ] Tests existants passent
- [ ] Nouveaux tests ajoutés
- [ ] Tests manuels effectués

## Checklist
- [ ] Code suit PSR-12
- [ ] Documentation mise à jour
- [ ] CHANGELOG.md mis à jour
- [ ] Pas de conflits avec main
```

### 6. Review

- Répondez aux commentaires de review
- Effectuez les modifications demandées
- Une fois approuvé, votre PR sera mergée !

---

## Rapporter des Bugs

### Template Bug Report

```markdown
**Description du bug**
Description claire du problème.

**Pour reproduire**
1. Aller à '...'
2. Cliquer sur '...'
3. Voir l'erreur

**Comportement attendu**
Ce qui devrait se passer.

**Screenshots**
Si applicable.

**Environnement**
- OS: [e.g. Ubuntu 22.04]
- PHP: [e.g. 8.2.10]
- PostgreSQL: [e.g. 15.3]
- Version: [e.g. 1.0.0]

**Logs**
```
Copier logs pertinents ici
```

**Contexte additionnel**
Toute information utile.
```

---

## Proposer des Fonctionnalités

### Template Feature Request

```markdown
**Problème à résoudre**
Description du problème ou besoin.

**Solution proposée**
Comment vous envisagez la solution.

**Alternatives considérées**
Autres approches possibles.

**Impact**
- Utilisateurs affectés: [tous/PME/Enterprise]
- Priorité: [basse/moyenne/haute]
- Effort estimé: [petit/moyen/large]

**Contexte additionnel**
Mockups, exemples, références, etc.
```

---

## Tests

### Écrire des Tests

```php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_invoice(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = ['customer_id' => 1, 'amount' => 100];

        // Act
        $response = $this->actingAs($user)
            ->postJson('/api/invoices', $data);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('invoices', ['amount' => 100]);
    }
}
```

### Lancer les Tests

```bash
# Tous les tests
php artisan test

# Feature tests seulement
php artisan test tests/Feature

# Unit tests seulement
php artisan test tests/Unit

# Test spécifique
php artisan test --filter=InvoiceTest

# Avec coverage
php artisan test --coverage --min=80
```

### Coverage Minimum

- **Global**: 80%
- **Controllers**: 90%
- **Services**: 95%
- **Models**: 85%

---

## Documentation

### Documenter le Code

```php
/**
 * Calculate the total amount including TVA and timbre fiscal.
 *
 * This method follows Tunisian tax regulations:
 * - TVA: 19% (or custom rate)
 * - Timbre Fiscal: 1% max 1 TND
 *
 * @param float $amountHT The amount excluding tax
 * @param float $tvaRate The TVA rate (default 19%)
 * @return array{amount_ht: float, tva: float, timbre: float, total: float}
 *
 * @example
 * $result = $this->calculateTotal(100.000, 19.00);
 * // Returns: ['amount_ht' => 100.000, 'tva' => 19.000, ...]
 */
public function calculateTotal(float $amountHT, float $tvaRate = 19.00): array
{
    // Implementation
}
```

### Mettre à Jour la Documentation

Lors de changements significatifs, mettez à jour :

- `README.md` : Overview et quick start
- `API_DOCUMENTATION.md` : Endpoints API
- `GETTING_STARTED.md` : Guide détaillé
- `CHANGELOG.md` : Historique des changements
- Docblocks dans le code

---

## Branches & Versions

### Branches Principales

- `main` : Code stable, production-ready
- `develop` : Développement actif (optionnel)
- `feature/*` : Nouvelles fonctionnalités
- `fix/*` : Corrections de bugs
- `hotfix/*` : Corrections urgentes en production

### Versioning

Nous suivons [Semantic Versioning](https://semver.org/):

- **MAJOR** (1.x.x) : Breaking changes
- **MINOR** (x.1.x) : Nouvelles fonctionnalités (backward compatible)
- **PATCH** (x.x.1) : Bug fixes

---

## Ressources Utiles

### Documentation Laravel
- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)

### Standards PHP
- [PSR-12](https://www.php-fig.org/psr/psr-12/)
- [PHP The Right Way](https://phptherightway.com/)

### Outils
- [PHPStan](https://phpstan.org/) : Static analysis
- [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) : Code style
- [Larastan](https://github.com/nunomaduro/larastan) : PHPStan for Laravel

---

## Questions ?

- **Email**: dev@tunisbusiness.tn
- **Discord**: [TunisBusiness Dev Community](https://discord.gg/tunisbusiness)
- **GitHub Discussions**: [Discussions](https://github.com/haythemsaa/facturation/discussions)

---

## Remerciements

Merci à tous les contributeurs qui rendent ce projet possible ! 🙏

Voir la liste complète : [Contributors](https://github.com/haythemsaa/facturation/graphs/contributors)

---

**Licence**: MIT
**Version**: 1.0.0
**Dernière mise à jour**: 19 Novembre 2025
