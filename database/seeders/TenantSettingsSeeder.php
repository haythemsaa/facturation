<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantSettings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default settings for all tenants without settings
        $tenants = Tenant::doesntHave('settings')->get();

        foreach ($tenants as $tenant) {
            TenantSettings::create([
                'tenant_id' => $tenant->id,
                'timezone' => 'Africa/Tunis',
                'currency' => 'TND',
                'default_tva_rate' => 19.00,
                'apply_timbre_fiscal' => true,
                'timbre_fiscal_threshold' => 0.01,
                'timbre_fiscal_rate' => 1.00,
                'max_timbre_fiscal' => 1.00,
                'invoice_prefix' => 'INV',
                'quote_prefix' => 'DEV',
                'delivery_note_prefix' => 'BL',
                'credit_note_prefix' => 'AV',
                'next_invoice_number' => 1,
                'next_quote_number' => 1,
                'next_delivery_note_number' => 1,
                'next_credit_note_number' => 1,
                'payment_terms_days' => 30,
                'default_payment_method' => 'bank_transfer',
                'bank_name' => null,
                'bank_account' => null,
                'bank_rib' => null,
                'smtp_host' => null,
                'smtp_port' => 587,
                'smtp_username' => null,
                'smtp_password' => null,
                'smtp_encryption' => 'tls',
                'email_from_address' => null,
                'email_from_name' => $tenant->company_name ?? $tenant->name,
                'low_stock_threshold' => 10,
                'enable_stock_alerts' => true,
                'default_sales_pipeline_id' => null,
                'enable_tour_geolocation' => true,
                'tour_check_in_radius' => 100,
                'enabled_notifications' => json_encode([
                    'low_stock' => true,
                    'invoice_payment' => true,
                    'opportunity_won' => true,
                    'subscription_expiring' => true,
                ]),
                'auto_lock_payslips' => true,
                'password_expires_days' => 90,
                'session_timeout_minutes' => 120,
            ]);
        }

        $this->command->info('Tenant settings created successfully for ' . $tenants->count() . ' tenant(s).');
    }
}
