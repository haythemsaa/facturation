# 🔒 Politique de Sécurité - TunisBusiness Suite

La sécurité de TunisBusiness Suite est notre priorité absolue. Ce document décrit notre politique de sécurité et comment signaler des vulnérabilités.

---

## 📋 Versions Supportées

| Version | Supportée          | Support jusqu'à |
| ------- | ------------------ | ---------------- |
| 1.0.x   | ✅ Oui             | Décembre 2026    |
| < 1.0   | ❌ Non             | N/A              |

---

## 🐛 Signaler une Vulnérabilité

### Processus de Divulgation Responsable

Si vous découvrez une vulnérabilité de sécurité, **NE LA PUBLIEZ PAS PUBLIQUEMENT**.

#### 1. Contactez-nous en Privé

**Email sécurisé** : security@tunisbusiness.tn
**PGP Key** : [Public Key](https://tunisbusiness.tn/.well-known/pgp-key.txt)

#### 2. Informations à Inclure

```markdown
**Type de vulnérabilité**
[SQL Injection, XSS, CSRF, Authentication Bypass, etc.]

**Sévérité estimée**
[Critique / Élevée / Moyenne / Basse]

**Description**
Description détaillée de la vulnérabilité.

**Étapes pour reproduire**
1. ...
2. ...
3. ...

**Preuve de concept (PoC)**
Code ou screenshot (si applicable).

**Impact potentiel**
Ce qui pourrait être compromis.

**Environnement**
- Version: 1.0.0
- OS: Ubuntu 22.04
- PHP: 8.2.10
- Configuration: Docker / Traditionnel

**Suggestions de correction** (optionnel)
Vos recommandations.
```

#### 3. Délais de Réponse

| Étape | Délai |
|-------|-------|
| **Accusé de réception** | < 24 heures |
| **Évaluation initiale** | < 72 heures |
| **Patch développé** | 7-30 jours (selon sévérité) |
| **Divulgation publique** | Après patch + 30 jours |

#### 4. Programme de Bug Bounty

🎁 **Récompenses pour vulnérabilités confirmées** :

| Sévérité | Récompense |
|----------|-----------|
| **Critique** | 500€ - 2000€ |
| **Élevée** | 200€ - 500€ |
| **Moyenne** | 50€ - 200€ |
| **Basse** | Reconnaissance publique |

---

## 🛡️ Mesures de Sécurité Implémentées

### 1. Authentication & Authorization

#### Sanctum (Bearer Tokens)
```php
// Tokens avec expiration
'expiration' => 120, // 2 heures

// Tokens révocables
$user->tokens()->delete();

// Rate limiting sur login
'login' => 10, // 10 tentatives/minute
```

#### RBAC (Spatie Permissions)
```php
// Permissions granulaires
$user->givePermissionTo('invoices.create');
$user->can('invoices.delete');

// Rôles hiérarchiques
'admin' > 'manager' > 'user'
```

### 2. Protection CSRF

```php
// Routes web protégées automatiquement
Route::middleware(['web'])->group(function () {
    // CSRF token requis
});

// API exemptée (Bearer token)
Route::middleware(['api', 'auth:sanctum']);
```

### 3. Protection XSS

```php
// Blade auto-escape
{{ $user->name }} // Échappé automatiquement

// Validation inputs
'email' => 'required|email|max:255',
'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
```

### 4. SQL Injection Prevention

```php
// Eloquent ORM (parameterized queries)
User::where('email', $email)->first(); // ✅ Sécurisé

// Query Builder
DB::table('users')->where('id', $id)->get(); // ✅ Sécurisé

// JAMAIS de raw queries avec input utilisateur
DB::raw("SELECT * FROM users WHERE id = $id"); // ❌ DANGEREUX
```

### 5. Rate Limiting

```php
// Tiers de rate limiting
'strict'    => 10 requests/min
'default'   => 60 requests/min
'relaxed'   => 200 requests/min
'unlimited' => 10000 requests/min

// Login spécifique
'login' => 10 requests/min
'register' => 5 requests/min
```

### 6. Multi-tenant Isolation

```php
// Scopes automatiques
protected static function booted()
{
    static::addGlobalScope('tenant', function (Builder $query) {
        $query->where('tenant_id', auth()->user()->tenant_id);
    });
}

// Tests d'isolation dans ExportTest.php
public function test_user_cannot_export_other_tenant_document()
{
    // Vérifie isolation stricte
}
```

### 7. Audit Logs

```php
// Traçabilité complète
AuditLog::create([
    'user_id' => auth()->id(),
    'action' => 'created',
    'auditable_type' => 'Invoice',
    'auditable_id' => $invoice->id,
    'old_values' => null,
    'new_values' => $invoice->toArray(),
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
]);
```

### 8. Encryption

```php
// Données sensibles chiffrées
use Illuminate\Support\Facades\Crypt;

// Chiffrer
$encrypted = Crypt::encryptString($sensitiveData);

// Déchiffrer
$decrypted = Crypt::decryptString($encrypted);
```

### 9. Password Hashing

```php
// Bcrypt avec 12 rounds
'bcrypt' => [
    'rounds' => env('BCRYPT_ROUNDS', 12),
],

// Hash automatique
Hash::make($password);

// Vérification
Hash::check($password, $hashedPassword);
```

### 10. HTTPS & HSTS

```nginx
# Force HTTPS
return 301 https://$server_name$request_uri;

# HSTS header
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
```

### 11. Security Headers

```nginx
# X-Frame-Options (Clickjacking)
add_header X-Frame-Options "SAMEORIGIN" always;

# X-Content-Type-Options (MIME sniffing)
add_header X-Content-Type-Options "nosniff" always;

# X-XSS-Protection
add_header X-XSS-Protection "1; mode=block" always;

# Referrer-Policy
add_header Referrer-Policy "no-referrer-when-downgrade" always;
```

### 12. CORS Configuration

```php
// Origines autorisées uniquement
'allowed_origins' => [
    'https://app.tunisbusiness.tn',
    'https://dashboard.tunisbusiness.tn',
],

// Méthodes autorisées
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

// Headers exposés
'exposed_headers' => ['Authorization'],
```

---

## 🔍 Tests de Sécurité

### Tests Automatisés

```bash
# Feature tests avec multi-tenant isolation
php artisan test tests/Feature/ExportTest.php

# Test: user_cannot_export_other_tenant_document
# Test: guest_cannot_export_document
# Test: user_cannot_export_document_with_invalid_dates
```

### Security Scanning (Recommandé)

```bash
# PHPStan (Static Analysis)
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse

# Psalm (Security-focused)
composer require --dev vimeo/psalm
./vendor/bin/psalm --show-info=true

# OWASP Dependency Check
composer audit

# SonarQube (CI/CD)
sonar-scanner
```

---

## 📚 Best Practices

### Pour les Développeurs

#### 1. Validation des Inputs

```php
// ✅ TOUJOURS valider
public function store(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|max:255',
        'amount' => 'required|numeric|min:0|max:999999.999',
    ]);
}

// ❌ JAMAIS d'input non validé
public function store(Request $request)
{
    $user = User::create($request->all()); // DANGEREUX
}
```

#### 2. Autorisation

```php
// ✅ Vérifier permissions
public function update(Request $request, Invoice $invoice)
{
    $this->authorize('update', $invoice);
    // ...
}

// ❌ Pas d'autorisation
public function update(Request $request, Invoice $invoice)
{
    $invoice->update($request->all()); // DANGEREUX
}
```

#### 3. Mass Assignment Protection

```php
// ✅ Whitelist avec $fillable
protected $fillable = ['name', 'email', 'phone'];

// Ou blacklist avec $guarded
protected $guarded = ['id', 'is_admin', 'password'];
```

#### 4. Secrets & Credentials

```php
// ✅ Variables d'environnement
$apiKey = env('STRIPE_SECRET');

// ❌ JAMAIS en dur dans code
$apiKey = 'sk_live_ABC123'; // DANGEREUX

// ❌ JAMAIS commité dans .env
# .gitignore DOIT contenir:
.env
.env.*
!.env.example
```

#### 5. File Uploads

```php
// ✅ Validation stricte
$request->validate([
    'document' => 'required|file|mimes:pdf,jpg,png|max:5120', // 5MB
]);

// ✅ Stockage sécurisé
$path = $request->file('document')->store('documents', 'private');

// ❌ Pas de validation
move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']); // DANGEREUX
```

### Pour les Administrateurs

#### 1. Gestion des Secrets

```bash
# ✅ Permissions strictes
chmod 600 .env
chown www-data:www-data .env

# ✅ Rotation régulière
# - APP_KEY: Jamais (sauf compromis)
# - DB_PASSWORD: Tous les 90 jours
# - API Keys: Tous les 90 jours

# ✅ Utiliser un vault (production)
# - HashiCorp Vault
# - AWS Secrets Manager
# - Azure Key Vault
```

#### 2. Mises à Jour

```bash
# Vérifier mises à jour
composer outdated

# Mettre à jour dépendances
composer update

# Auditer vulnérabilités
composer audit

# Automatiser (CI/CD)
dependabot.yml
```

#### 3. Monitoring

```bash
# Logs de sécurité
tail -f storage/logs/laravel.log | grep "ALERT\|ERROR\|CRITICAL"

# Tentatives login échouées
grep "Failed login" storage/logs/laravel.log | wc -l

# Accès non autorisés (403)
tail -f /var/log/nginx/access.log | grep " 403 "
```

#### 4. Backups

```bash
# Chiffrer backups
gpg --encrypt --recipient admin@tunisbusiness.tn backup.sql

# Stockage offsite
aws s3 cp backup.sql.gpg s3://tunisbusiness-backups/ --sse AES256

# Tester restauration (mensuel)
psql -U user -d test_db < backup.sql
```

---

## 🚨 Incidents de Sécurité

### En Cas d'Incident

1. **Contenir** : Isoler le système compromis
2. **Analyser** : Déterminer l'étendue de la compromission
3. **Notifier** : Informer les parties affectées (RGPD : < 72h)
4. **Corriger** : Appliquer le patch
5. **Documenter** : Post-mortem détaillé

### Plan de Réponse

```markdown
## Incident Response Plan

### Phase 1: Détection (0-1h)
- [ ] Alertes monitoring déclenchées
- [ ] Équipe sécurité notifiée
- [ ] Incident confirmé

### Phase 2: Confinement (1-4h)
- [ ] Système isolé
- [ ] Accès révoqués
- [ ] Logs sauvegardés

### Phase 3: Éradication (4-24h)
- [ ] Cause identifiée
- [ ] Vulnérabilité corrigée
- [ ] Systèmes nettoyés

### Phase 4: Récupération (24-48h)
- [ ] Services restaurés
- [ ] Surveillance accrue
- [ ] Tests de sécurité

### Phase 5: Post-Incident (48h+)
- [ ] Post-mortem rédigé
- [ ] Mesures préventives
- [ ] Équipe formée
```

---

## 📞 Contact Sécurité

### Équipe Sécurité

**Email** : security@tunisbusiness.tn
**PGP Key** : [Download](https://tunisbusiness.tn/.well-known/pgp-key.txt)
**Téléphone** : +216 XX XXX XXX (urgences uniquement)

### Signaler un Incident

**URL** : https://tunisbusiness.tn/security/report
**Formulaire** : Chiffré end-to-end

---

## 🏆 Hall of Fame

Nous remercions ces chercheurs en sécurité pour leurs contributions :

| Chercheur | Vulnérabilité | Date | Récompense |
|-----------|---------------|------|------------|
| *À venir* | - | - | - |

---

## 📖 Ressources

### Standards & Frameworks

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP API Security Top 10](https://owasp.org/www-project-api-security/)
- [CWE Top 25](https://cwe.mitre.org/top25/)
- [RGPD](https://www.cnil.fr/fr/reglement-europeen-protection-donnees)

### Outils

- [Burp Suite](https://portswigger.net/burp) : Web security testing
- [OWASP ZAP](https://www.zaproxy.org/) : Vulnerability scanner
- [Nikto](https://cirt.net/Nikto2) : Web server scanner
- [SQLMap](https://sqlmap.org/) : SQL injection tool

### Formation

- [OWASP WebGoat](https://owasp.org/www-project-webgoat/)
- [HackTheBox](https://www.hackthebox.com/)
- [TryHackMe](https://tryhackme.com/)

---

## 📜 Conformité

### RGPD (Europe)

- ✅ Droit à l'oubli implémenté
- ✅ Portabilité des données
- ✅ Consentement explicite
- ✅ Notification de breach < 72h

### Loi Tunisienne (Loi n° 2004-63)

- ✅ Protection données personnelles
- ✅ Déclaration INPDP
- ✅ Sécurité des données

### SOC 2 (En cours)

- 🔄 Security
- 🔄 Availability
- 🔄 Confidentiality

---

**Dernière mise à jour** : 19 Novembre 2025
**Version politique** : 1.0
**Contact** : security@tunisbusiness.tn
