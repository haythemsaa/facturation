# TunisBusiness Suite - API Documentation

Version 1.0.0 | Dernière mise à jour: 17 Novembre 2025

## 📋 Table des Matières

- [Introduction](#introduction)
- [Authentification](#authentification)
- [Rate Limiting](#rate-limiting)
- [Health & Monitoring](#health--monitoring)
- [Stock & Facturation](#stock--facturation)
- [CRM](#crm)
- [HR & Paie](#hr--paie)
- [Notifications](#notifications)
- [Exports](#exports)
- [Webhooks](#webhooks)
- [Codes d'Erreur](#codes-derreur)

---

## 🚀 Introduction

L'API TunisBusiness Suite est une API RESTful qui utilise JSON pour les requêtes et réponses.

**Base URL:** `https://api.tunisbusiness.tn/api`

**Version:** v1

**Authentification:** Bearer Token (Laravel Sanctum)

---

## 🔐 Authentification

### Login

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "tenant_id": 1
  }
}
```

### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

### Utilisation Token

Tous les endpoints protégés requièrent le header:

```http
Authorization: Bearer {token}
```

---

## ⚡ Rate Limiting

L'API applique automatiquement des limites de taux par user+tenant:

| Tier | Limite | Usage |
|------|--------|-------|
| **strict** | 10 req/min | Endpoints sensibles (validation, paiement) |
| **default** | 60 req/min | Usage normal |
| **relaxed** | 200 req/min | Lectures massives (exports, rapports) |
| **unlimited** | 10,000 req/min | Admin/Tests |

**Headers de réponse:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 42
Retry-After: 30 (si limite dépassée)
```

**Response 429 (Too Many Requests):**
```json
{
  "message": "Too many requests. Please slow down.",
  "retry_after": 42
}
```

---

## 🏥 Health & Monitoring

### Basic Health Check

```http
GET /api/health
```

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-11-17T21:00:00.000000Z",
  "service": "TunisBusiness Suite",
  "version": "1.0.0"
}
```

### Detailed Health Check

```http
GET /api/health/detailed
```

**Response:**
```json
{
  "status": "healthy",
  "timestamp": "2025-11-17T21:00:00.000000Z",
  "checks": {
    "database": {
      "status": "healthy",
      "connection": "pgsql",
      "tables_count": 45,
      "response_time": "15.23ms"
    },
    "cache": {
      "status": "healthy",
      "driver": "redis",
      "write_read": "ok"
    },
    "storage": {
      "status": "healthy",
      "disk": "local",
      "writable": true
    },
    "queue": {
      "status": "healthy",
      "driver": "redis"
    }
  },
  "system": {
    "php_version": "8.2.12",
    "laravel_version": "11.x",
    "environment": "production",
    "debug_mode": false
  }
}
```

### System Metrics

```http
GET /api/health/metrics
```

**Response:**
```json
{
  "timestamp": "2025-11-17T21:00:00.000000Z",
  "metrics": {
    "tenants": {
      "total": 150,
      "active": 142
    },
    "users": {
      "total": 850
    },
    "subscriptions": {
      "active": 142,
      "expired": 5,
      "cancelled": 3
    },
    "documents": {
      "total": 12450,
      "invoices": 8200,
      "validated": 7850
    },
    "storage": {
      "database_size": "2.45 GB"
    }
  }
}
```

---

## 📦 Stock & Facturation

### Products

#### List Products

```http
GET /api/products?page=1&per_page=50&search=laptop&category_id=5
```

**Query Parameters:**
- `page` (int): Page number
- `per_page` (int): Items per page (max 100)
- `search` (string): Search in name/code/barcode
- `category_id` (int): Filter by category
- `is_active` (boolean): Filter active/inactive

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "code": "PROD-001",
      "name": "Laptop Dell XPS 15",
      "description": "...",
      "type": "product",
      "unit": "piece",
      "purchase_price": 2500.000,
      "selling_price": 3200.000,
      "tva_rate": 19.00,
      "stock_alert_threshold": 5.000,
      "track_stock": true,
      "is_active": true,
      "category": {...},
      "created_at": "2025-11-01T10:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 156,
    "per_page": 50
  }
}
```

#### Create Product

```http
POST /api/products
Content-Type: application/json

{
  "category_id": 5,
  "code": "PROD-002",
  "name": "iPhone 15 Pro",
  "description": "Latest model",
  "type": "product",
  "unit": "piece",
  "purchase_price": 3500.000,
  "selling_price": 4500.000,
  "minimum_price": 4000.000,
  "tva_rate": 19.00,
  "stock_alert_threshold": 10.000,
  "track_stock": true,
  "is_active": true
}
```

#### Update Product

```http
PUT /api/products/{id}
```

#### Delete Product

```http
DELETE /api/products/{id}
```

### Documents (Invoices, Quotes, etc.)

#### List Documents

```http
GET /api/documents?type=invoice&status=validated&start_date=2025-01-01&end_date=2025-12-31
```

**Query Parameters:**
- `type`: invoice, quote, delivery_note, credit_note, purchase_order
- `status`: draft, sent, validated, cancelled
- `payment_status`: pending, partial, paid
- `customer_id`: Filter by customer
- `start_date`, `end_date`: Date range

#### Create Invoice

```http
POST /api/documents
Content-Type: application/json

{
  "type": "invoice",
  "date": "2025-11-17",
  "due_date": "2025-12-17",
  "customer_id": 10,
  "warehouse_id": 1,
  "discount_rate": 5.00,
  "note": "Merci pour votre confiance",
  "lines": [
    {
      "product_id": 5,
      "product_name": "Laptop Dell XPS 15",
      "quantity": 2,
      "unit_price": 3200.000,
      "tva_rate": 19.00
    },
    {
      "product_id": 12,
      "product_name": "Mouse Logitech",
      "quantity": 5,
      "unit_price": 45.000,
      "tva_rate": 19.00
    }
  ]
}
```

**Response:** Document créé avec totaux calculés automatiquement

#### Validate Document

```http
POST /api/documents/{id}/validate
```

**Effect:**
- Marque comme validé
- Génère mouvements de stock
- Déclenche événement DocumentValidated
- Envoie notifications

---

## 🤝 CRM

### Contacts

#### List Contacts

```http
GET /api/contacts?type=customer&assigned_to=5&score_min=70
```

**Query Parameters:**
- `type`: lead, prospect, customer
- `assigned_to`: User ID
- `source`: Source d'acquisition
- `score_min`, `score_max`: Range de score

#### Create Contact

```http
POST /api/contacts
Content-Type: application/json

{
  "type": "lead",
  "first_name": "Ahmed",
  "last_name": "Ben Ali",
  "email": "ahmed@example.com",
  "phone": "+216 20 123 456",
  "company": "Tech Solutions SARL",
  "job_title": "CEO",
  "source": "website",
  "score": 75,
  "assigned_to": 5
}
```

### Opportunities

#### List Opportunities

```http
GET /api/opportunities?status=open&pipeline_id=1
```

#### Create Opportunity

```http
POST /api/opportunities
Content-Type: application/json

{
  "contact_id": 15,
  "pipeline_id": 1,
  "stage_id": 3,
  "title": "Projet ERP 50 utilisateurs",
  "value": 50000.000,
  "probability": 70,
  "expected_close_date": "2026-01-15",
  "status": "open"
}
```

#### Mark as Won

```http
POST /api/opportunities/{id}/won
```

**Effect:**
- Status → won
- actual_close_date → now()
- Contact converti en customer (si lead/prospect)
- Événement OpportunityWon dispatché

#### Mark as Lost

```http
POST /api/opportunities/{id}/lost
Content-Type: application/json

{
  "lost_reason": "Budget insuffisant"
}
```

---

## 👥 HR & Paie

### Employees

#### List Employees

```http
GET /api/employees?status=active&department_id=3
```

#### Create Employee

```http
POST /api/employees
Content-Type: application/json

{
  "employee_number": "EMP-2025-001",
  "first_name": "Mohamed",
  "last_name": "Trabelsi",
  "cin": "12345678",
  "cnss_number": "1234567890",
  "birth_date": "1990-05-15",
  "gender": "male",
  "email": "mohamed.t@company.tn",
  "phone": "+216 25 123 456",
  "department_id": 3,
  "position_id": 8,
  "hire_date": "2025-01-01",
  "status": "active"
}
```

### Payslips

#### Generate Payslip

```http
POST /api/payslips
Content-Type: application/json

{
  "employee_id": 25,
  "month": 11,
  "year": 2025,
  "base_salary": 1500.000,
  "allowances": 200.000,
  "overtime_hours": 10,
  "overtime_rate": 15.000
}
```

**Response:** Bulletin avec calculs CNSS/IRPP automatiques

---

## 🔔 Notifications

### List Notifications

```http
GET /api/notifications?unread_only=true&type=opportunity_won&per_page=20
```

### Get Unread Count

```http
GET /api/notifications/unread-count
```

**Response:**
```json
{
  "unread_count": 12
}
```

### Mark as Read

```http
POST /api/notifications/{id}/read
```

### Mark All as Read

```http
POST /api/notifications/mark-all-read
```

### Delete Notification

```http
DELETE /api/notifications/{id}
```

---

## 📊 Exports

### Export Document PDF

```http
GET /api/export/document/{id}/pdf?action=download&template_id=5
```

**Query Parameters:**
- `action`: download (default) ou stream (inline)
- `template_id`: ID du template personnalisé (optionnel)

**Response:** Binary PDF file

### Export Bulk PDF

```http
POST /api/export/bulk-pdf
Content-Type: application/json

{
  "document_ids": [1, 5, 12, 25]
}
```

**Response:** Single PDF avec tous les documents

### Export Sales Report Excel

```http
GET /api/export/sales/excel?start_date=2025-01-01&end_date=2025-12-31
```

**Response:** Excel file (.xlsx)

### Export Stock Report Excel

```http
GET /api/export/stock/excel
```

**Includes:**
- Valorisation stock
- Highlight stocks faibles (rouge)
- Totaux automatiques

### Export Contacts Excel

```http
GET /api/export/contacts/excel?type=customer&assigned_to=5
```

### Export Audit Logs Excel

```http
GET /api/export/audit-logs/excel?start_date=2025-11-01&end_date=2025-11-17
```

**Limit:** 5000 lignes max

---

## 🔗 Webhooks

### Create Webhook

```http
POST /api/webhooks
Content-Type: application/json

{
  "name": "Zapier Integration",
  "url": "https://hooks.zapier.com/hooks/catch/xxx/yyy",
  "events": [
    "document.validated",
    "opportunity.won",
    "subscription.expiring"
  ],
  "is_active": true
}
```

**Response:**
```json
{
  "id": 1,
  "name": "Zapier Integration",
  "url": "https://hooks.zapier.com/...",
  "events": ["document.validated", "opportunity.won"],
  "secret": "abc123...",
  "is_active": true,
  "max_retries": 3,
  "success_count": 0,
  "failure_count": 0
}
```

### Webhook Payload Format

```json
{
  "event": "document.validated",
  "timestamp": "2025-11-17T21:00:00.000000Z",
  "tenant_id": 1,
  "data": {
    "document": {
      "id": 123,
      "type": "invoice",
      "number": "INV-202511-0123",
      "total_ttc": 5432.150,
      "customer": {...}
    }
  }
}
```

**Security:** Header `X-Webhook-Signature` avec HMAC-SHA256

---

## ❌ Codes d'Erreur

| Code | Message | Description |
|------|---------|-------------|
| 200 | OK | Requête réussie |
| 201 | Created | Ressource créée |
| 204 | No Content | Suppression réussie |
| 400 | Bad Request | Paramètres invalides |
| 401 | Unauthorized | Non authentifié |
| 403 | Forbidden | Non autorisé |
| 404 | Not Found | Ressource introuvable |
| 422 | Unprocessable Entity | Erreur de validation |
| 429 | Too Many Requests | Rate limit dépassé |
| 500 | Internal Server Error | Erreur serveur |
| 503 | Service Unavailable | Service indisponible |

### Format Erreur Standard

```json
{
  "message": "Description de l'erreur",
  "errors": {
    "email": ["Le champ email est obligatoire"],
    "amount": ["Le montant doit être positif"]
  }
}
```

---

## 📞 Support

- 📧 Email: api-support@tunisbusiness.tn
- 💬 Discord: [Community](https://discord.gg/tunisbusiness)
- 📚 Docs: [docs.tunisbusiness.tn](https://docs.tunisbusiness.tn)
- 🐛 Issues: [GitHub](https://github.com/tunisbusiness/issues)

---

## 📝 Changelog

### Version 1.0.0 (2025-11-17)

- Initial API release
- 150+ endpoints
- Complete CRUD operations
- Health checks
- Rate limiting
- Webhooks
- PDF/Excel exports
- Multi-language support

---

<div align="center">

**TunisBusiness Suite API** | Made with ❤️ in Tunisia 🇹🇳

</div>
