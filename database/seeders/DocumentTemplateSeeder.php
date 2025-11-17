<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\DocumentTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Check if templates already exist for this tenant
            if (DocumentTemplate::where('tenant_id', $tenant->id)->exists()) {
                continue;
            }

            // Invoice Template
            DocumentTemplate::create([
                'tenant_id' => $tenant->id,
                'name' => 'Template Facture Standard',
                'type' => 'invoice',
                'body_html' => $this->getInvoiceTemplate(),
                'styles' => json_encode([
                    'primary_color' => '#3b82f6',
                    'secondary_color' => '#1e3a8a',
                    'font_family' => 'Arial, sans-serif',
                ]),
                'is_default' => true,
            ]);

            // Quote Template
            DocumentTemplate::create([
                'tenant_id' => $tenant->id,
                'name' => 'Template Devis Standard',
                'type' => 'quote',
                'body_html' => $this->getQuoteTemplate(),
                'styles' => json_encode([
                    'primary_color' => '#10b981',
                    'secondary_color' => '#065f46',
                    'font_family' => 'Arial, sans-serif',
                ]),
                'is_default' => true,
            ]);

            // Delivery Note Template
            DocumentTemplate::create([
                'tenant_id' => $tenant->id,
                'name' => 'Template Bon de Livraison Standard',
                'type' => 'delivery_note',
                'body_html' => $this->getDeliveryNoteTemplate(),
                'styles' => json_encode([
                    'primary_color' => '#f59e0b',
                    'secondary_color' => '#92400e',
                    'font_family' => 'Arial, sans-serif',
                ]),
                'is_default' => true,
            ]);
        }

        $this->command->info('Document templates created successfully for ' . $tenants->count() . ' tenant(s).');
    }

    private function getInvoiceTemplate(): string
    {
        return '
<div class="document-header" style="text-align: center; border-bottom: 3px solid {{primary_color}}; padding-bottom: 20px; margin-bottom: 30px;">
    <h1 style="color: {{primary_color}}; font-size: 32px; margin: 0;">FACTURE</h1>
    <p style="font-size: 18px; color: #666;">N° {{document.number}}</p>
</div>

<div class="company-info" style="display: flex; justify-content: space-between; margin-bottom: 30px;">
    <div>
        <h3 style="color: {{secondary_color}};">{{tenant.name}}</h3>
        <p>{{tenant.address}}<br>
        {{tenant.city}}, {{tenant.postal_code}}<br>
        MF: {{tenant.tax_number}}</p>
    </div>
    <div style="text-align: right;">
        <h3 style="color: {{secondary_color}};">Client</h3>
        <p><strong>{{customer.name}}</strong><br>
        {{customer.address}}<br>
        {{customer.city}}<br>
        MF: {{customer.tax_number}}</p>
    </div>
</div>

<div class="document-dates" style="margin-bottom: 30px;">
    <p><strong>Date:</strong> {{document.date}}</p>
    <p><strong>Date d\'échéance:</strong> {{document.due_date}}</p>
</div>

<table class="items-table" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
    <thead>
        <tr style="background-color: {{primary_color}}; color: white;">
            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Désignation</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">Qté</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">P.U. HT</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">TVA</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Total HT</th>
        </tr>
    </thead>
    <tbody>
        {{items_loop}}
    </tbody>
</table>

<div class="totals" style="text-align: right; margin-top: 30px;">
    <p><strong>Total HT:</strong> {{document.total_ht}} TND</p>
    <p><strong>TVA:</strong> {{document.total_tva}} TND</p>
    <p><strong>Timbre Fiscal:</strong> {{document.timbre_fiscal}} TND</p>
    <p style="font-size: 20px; color: {{primary_color}};"><strong>Total TTC:</strong> {{document.total_ttc}} TND</p>
</div>

<div class="footer" style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
    <p>Conditions de paiement: {{document.payment_terms}} jours</p>
    <p>Merci pour votre confiance!</p>
</div>';
    }

    private function getQuoteTemplate(): string
    {
        return '
<div class="document-header" style="text-align: center; border-bottom: 3px solid {{primary_color}}; padding-bottom: 20px; margin-bottom: 30px;">
    <h1 style="color: {{primary_color}}; font-size: 32px; margin: 0;">DEVIS</h1>
    <p style="font-size: 18px; color: #666;">N° {{document.number}}</p>
</div>

<div class="company-info" style="display: flex; justify-content: space-between; margin-bottom: 30px;">
    <div>
        <h3 style="color: {{secondary_color}};">{{tenant.name}}</h3>
        <p>{{tenant.address}}<br>
        {{tenant.city}}, {{tenant.postal_code}}</p>
    </div>
    <div style="text-align: right;">
        <h3 style="color: {{secondary_color}};">Client</h3>
        <p><strong>{{customer.name}}</strong><br>
        {{customer.address}}<br>
        {{customer.city}}</p>
    </div>
</div>

<div class="document-dates" style="margin-bottom: 30px;">
    <p><strong>Date:</strong> {{document.date}}</p>
    <p><strong>Validité:</strong> {{document.validity_days}} jours</p>
</div>

<table class="items-table" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
    <thead>
        <tr style="background-color: {{primary_color}}; color: white;">
            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Désignation</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">Qté</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">P.U. HT</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">TVA</th>
            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Total HT</th>
        </tr>
    </thead>
    <tbody>
        {{items_loop}}
    </tbody>
</table>

<div class="totals" style="text-align: right; margin-top: 30px;">
    <p><strong>Total HT:</strong> {{document.total_ht}} TND</p>
    <p><strong>TVA:</strong> {{document.total_tva}} TND</p>
    <p style="font-size: 20px; color: {{primary_color}};"><strong>Total TTC:</strong> {{document.total_ttc}} TND</p>
</div>

<div class="footer" style="margin-top: 50px; text-align: center; font-size: 12px; color: #666;">
    <p>Ce devis est valable {{document.validity_days}} jours</p>
    <p>En attente de votre retour!</p>
</div>';
    }

    private function getDeliveryNoteTemplate(): string
    {
        return '
<div class="document-header" style="text-align: center; border-bottom: 3px solid {{primary_color}}; padding-bottom: 20px; margin-bottom: 30px;">
    <h1 style="color: {{primary_color}}; font-size: 32px; margin: 0;">BON DE LIVRAISON</h1>
    <p style="font-size: 18px; color: #666;">N° {{document.number}}</p>
</div>

<div class="company-info" style="display: flex; justify-content: space-between; margin-bottom: 30px;">
    <div>
        <h3 style="color: {{secondary_color}};">{{tenant.name}}</h3>
        <p>{{tenant.address}}<br>
        {{tenant.city}}, {{tenant.postal_code}}</p>
    </div>
    <div style="text-align: right;">
        <h3 style="color: {{secondary_color}};">Client</h3>
        <p><strong>{{customer.name}}</strong><br>
        {{customer.address}}<br>
        {{customer.city}}</p>
    </div>
</div>

<div class="document-dates" style="margin-bottom: 30px;">
    <p><strong>Date de livraison:</strong> {{document.date}}</p>
</div>

<table class="items-table" style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
    <thead>
        <tr style="background-color: {{primary_color}}; color: white;">
            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Désignation</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #ddd;">Quantité</th>
            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Observation</th>
        </tr>
    </thead>
    <tbody>
        {{items_loop}}
    </tbody>
</table>

<div class="signatures" style="display: flex; justify-content: space-between; margin-top: 80px;">
    <div style="text-align: center; width: 45%; border-top: 1px solid #000; padding-top: 10px;">
        <p><strong>Signature Livreur</strong></p>
    </div>
    <div style="text-align: center; width: 45%; border-top: 1px solid #000; padding-top: 10px;">
        <p><strong>Signature Client</strong></p>
    </div>
</div>';
    }
}
