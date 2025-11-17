# TunisBusiness Suite - Documentation API

## Base URL
```
http://localhost:8000/api
```

## Authentication
Toutes les routes (sauf login/register) nécessitent un token Bearer dans le header:
```
Authorization: Bearer {token}
```

## Routes API

### 🔐 Authentication

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/register` | Inscription |
| POST | `/login` | Connexion |
| POST | `/logout` | Déconnexion |
| GET | `/me` | Utilisateur connecté |

### 📦 Module Stock & Facturation

#### Produits
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/products` | Liste des produits |
| POST | `/stock/products` | Créer un produit |
| GET | `/stock/products/{id}` | Détails produit |
| PUT | `/stock/products/{id}` | Modifier produit |
| DELETE | `/stock/products/{id}` | Supprimer produit |
| GET | `/stock/products/{id}/stock` | Stock du produit |

#### Catégories
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/categories` | Liste des catégories |
| POST | `/stock/categories` | Créer catégorie |
| GET | `/stock/categories/{id}` | Détails catégorie |
| PUT | `/stock/categories/{id}` | Modifier catégorie |
| DELETE | `/stock/categories/{id}` | Supprimer catégorie |

#### Dépôts
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/warehouses` | Liste des dépôts |
| POST | `/stock/warehouses` | Créer dépôt |
| GET | `/stock/warehouses/{id}` | Détails dépôt |
| GET | `/stock/warehouses/{id}/stock` | Stock du dépôt |

#### Gestion Stock
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/stocks` | Liste des stocks |
| POST | `/stock/stocks/movement` | Créer mouvement |
| GET | `/stock/stocks/movements` | Historique mouvements |
| POST | `/stock/stocks/transfer` | Transfert entre dépôts |
| POST | `/stock/stocks/adjustment` | Ajustement de stock |

#### Clients
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/customers` | Liste des clients |
| POST | `/stock/customers` | Créer client |
| GET | `/stock/customers/{id}` | Détails client |
| PUT | `/stock/customers/{id}` | Modifier client |
| DELETE | `/stock/customers/{id}` | Supprimer client |
| GET | `/stock/customers/{id}/documents` | Documents du client |
| GET | `/stock/customers/{id}/balance` | Balance âgée |

#### Fournisseurs
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/suppliers` | Liste des fournisseurs |
| POST | `/stock/suppliers` | Créer fournisseur |
| GET | `/stock/suppliers/{id}` | Détails fournisseur |
| PUT | `/stock/suppliers/{id}` | Modifier fournisseur |
| DELETE | `/stock/suppliers/{id}` | Supprimer fournisseur |

#### Documents (Devis, BL, Factures)
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/documents` | Liste des documents |
| POST | `/stock/documents` | Créer document |
| GET | `/stock/documents/{id}` | Détails document |
| DELETE | `/stock/documents/{id}` | Supprimer document |
| POST | `/stock/documents/{id}/validate` | Valider document |
| POST | `/stock/documents/{id}/send` | Envoyer document |
| POST | `/stock/documents/{id}/payment` | Ajouter paiement |
| GET | `/stock/documents/{id}/pdf` | Télécharger PDF |

#### Rapports
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/stock/reports/stock-valuation` | Valorisation du stock |
| GET | `/stock/reports/sales` | Rapport des ventes |
| GET | `/stock/reports/purchases` | Rapport des achats |
| GET | `/stock/reports/tva` | État de TVA |

### 👥 Module CRM

#### Contacts & Leads
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/contacts` | Liste des contacts |
| POST | `/crm/contacts` | Créer contact |
| GET | `/crm/contacts/{id}` | Détails contact |
| PUT | `/crm/contacts/{id}` | Modifier contact |
| DELETE | `/crm/contacts/{id}` | Supprimer contact |
| POST | `/crm/contacts/{id}/convert` | Convertir en client |
| POST | `/crm/contacts/{id}/qualify` | Qualifier lead |

#### Pipelines
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/pipelines` | Liste des pipelines |
| POST | `/crm/pipelines` | Créer pipeline |
| GET | `/crm/pipelines/{id}` | Détails pipeline |
| POST | `/crm/pipelines/{id}/stages` | Ajouter étape |

#### Opportunités
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/opportunities` | Liste des opportunités |
| POST | `/crm/opportunities` | Créer opportunité |
| GET | `/crm/opportunities/{id}` | Détails opportunité |
| PUT | `/crm/opportunities/{id}` | Modifier opportunité |
| DELETE | `/crm/opportunities/{id}` | Supprimer opportunité |
| POST | `/crm/opportunities/{id}/move` | Déplacer étape |
| POST | `/crm/opportunities/{id}/win` | Marquer comme gagnée |
| POST | `/crm/opportunities/{id}/lose` | Marquer comme perdue |

#### Activités
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/activities` | Liste des activités |
| POST | `/crm/activities` | Créer activité |
| GET | `/crm/activities/{id}` | Détails activité |
| PUT | `/crm/activities/{id}` | Modifier activité |
| DELETE | `/crm/activities/{id}` | Supprimer activité |
| POST | `/crm/activities/{id}/complete` | Marquer comme terminée |
| GET | `/crm/activities/calendar` | Vue calendrier |

#### Tournées Commerciales
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/tours` | Liste des tournées |
| POST | `/crm/tours` | Créer tournée |
| GET | `/crm/tours/{id}` | Détails tournée |
| POST | `/crm/tours/{id}/start` | Démarrer tournée |
| POST | `/crm/tours/{id}/complete` | Terminer tournée |
| POST | `/crm/tours/{id}/visits/{visit}/checkin` | Check-in visite |
| POST | `/crm/tours/{id}/visits/{visit}/checkout` | Check-out visite |

#### Objectifs
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/targets` | Liste des objectifs |
| POST | `/crm/targets` | Créer objectif |
| GET | `/crm/targets/progress` | Progression des objectifs |

#### Rapports
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/crm/reports/pipeline` | Rapport du pipeline |
| GET | `/crm/reports/sales-performance` | Performance commerciale |
| GET | `/crm/reports/conversion` | Taux de conversion |

### 💼 Module RH & Paie

#### Départements
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/departments` | Liste des départements |
| POST | `/hr/departments` | Créer département |
| GET | `/hr/departments/{id}` | Détails département |
| PUT | `/hr/departments/{id}` | Modifier département |

#### Postes
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/positions` | Liste des postes |
| POST | `/hr/positions` | Créer poste |
| GET | `/hr/positions/{id}` | Détails poste |

#### Employés
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/employees` | Liste des employés |
| POST | `/hr/employees` | Créer employé |
| GET | `/hr/employees/{id}` | Détails employé |
| PUT | `/hr/employees/{id}` | Modifier employé |
| DELETE | `/hr/employees/{id}` | Supprimer employé |
| GET | `/hr/employees/{id}/contracts` | Contrats de l'employé |

#### Contrats
| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/hr/employees/{id}/contracts` | Créer contrat |
| GET | `/hr/contracts/{id}` | Détails contrat |
| POST | `/hr/contracts/{id}/amendments` | Ajouter avenant |

#### Pointages
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/attendances` | Liste des pointages |
| POST | `/hr/attendances` | Créer pointage |
| POST | `/hr/attendances/checkin` | Check-in |
| POST | `/hr/attendances/checkout` | Check-out |
| GET | `/hr/attendances/summary` | Résumé pointages |

#### Congés
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/leave-types` | Types de congés |
| POST | `/hr/leave-types` | Créer type |
| GET | `/hr/leave-requests` | Demandes de congés |
| POST | `/hr/leave-requests` | Créer demande |
| POST | `/hr/leave-requests/{id}/approve` | Approuver demande |
| POST | `/hr/leave-requests/{id}/reject` | Rejeter demande |
| GET | `/hr/leave-balances` | Soldes de congés |

#### Bulletins de Paie
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/payslips` | Liste des bulletins |
| POST | `/hr/payslips` | Créer bulletin |
| GET | `/hr/payslips/{id}` | Détails bulletin |
| DELETE | `/hr/payslips/{id}` | Supprimer bulletin |
| POST | `/hr/payslips/{id}/validate` | Valider bulletin |
| POST | `/hr/payslips/{id}/pay` | Marquer comme payé |
| GET | `/hr/payslips/{id}/pdf` | Télécharger PDF |
| POST | `/hr/payslips/generate-batch` | Génération en masse |

#### Déclarations Sociales
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/declarations/cnss` | Déclarations CNSS |
| POST | `/hr/declarations/cnss` | Générer déclaration CNSS |
| GET | `/hr/declarations/cnss/{id}` | Détails déclaration CNSS |
| POST | `/hr/declarations/cnss/{id}/submit` | Soumettre déclaration |
| GET | `/hr/declarations/cnss/{id}/export` | Exporter e-CNSS |
| GET | `/hr/declarations/irpp` | Déclarations IRPP |
| POST | `/hr/declarations/irpp` | Générer déclaration IRPP |
| GET | `/hr/declarations/irpp/{id}` | Détails déclaration IRPP |

#### Rapports RH
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/hr/reports/payroll-summary` | Résumé de paie |
| GET | `/hr/reports/attendance-summary` | Résumé des présences |
| GET | `/hr/reports/leave-summary` | Résumé des congés |

### 📊 Dashboard
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/dashboard/stats` | Statistiques générales |
| GET | `/dashboard/charts` | Données graphiques |

### ⚙️ Administration
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/admin/users` | Liste des utilisateurs |
| POST | `/admin/users` | Créer utilisateur |
| PUT | `/admin/users/{id}` | Modifier utilisateur |
| GET | `/admin/roles` | Liste des rôles |
| POST | `/admin/roles` | Créer rôle |
| GET | `/admin/permissions` | Liste des permissions |
| GET | `/admin/tenant` | Informations tenant |
| PUT | `/admin/tenant` | Modifier tenant |
| GET | `/admin/subscription` | Abonnement actif |

## Codes de Réponse HTTP

- `200 OK` - Succès
- `201 Created` - Ressource créée
- `204 No Content` - Succès sans contenu
- `400 Bad Request` - Requête invalide
- `401 Unauthorized` - Non authentifié
- `403 Forbidden` - Accès refusé
- `404 Not Found` - Ressource non trouvée
- `422 Unprocessable Entity` - Erreur de validation
- `500 Internal Server Error` - Erreur serveur

## Format des Réponses

### Succès
```json
{
  "data": {},
  "message": "Success"
}
```

### Erreur de Validation
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message"
    ]
  }
}
```

### Pagination
```json
{
  "data": [],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

## Exemples de Requêtes

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@tunisbusiness.tn",
    "password": "password"
  }'
```

### Créer un Produit
```bash
curl -X POST http://localhost:8000/api/stock/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "ART-001",
    "name": "Produit Test",
    "type": "product",
    "unit": "piece",
    "purchase_price": 50.000,
    "selling_price": 100.000,
    "tva_rate": "19",
    "track_stock": true
  }'
```

### Créer une Facture
```bash
curl -X POST http://localhost:8000/api/stock/documents \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "invoice",
    "number": "FAC-2025-001",
    "date": "2025-11-17",
    "customer_id": 1,
    "lines": [
      {
        "product_id": 1,
        "quantity": 5,
        "unit_price": 100.000,
        "tva_rate": "19"
      }
    ]
  }'
```

### Créer un Bulletin de Paie
```bash
curl -X POST http://localhost:8000/api/hr/payslips \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": 1,
    "month": 11,
    "year": 2025,
    "worked_days": 26
  }'
```
