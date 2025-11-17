<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'timezone',
        'date_format',
        'time_format',
        'currency',
        'language',
        'invoice_prefix',
        'quote_prefix',
        'invoice_next_number',
        'quote_next_number',
        'invoice_terms',
        'invoice_footer',
        'default_tva_rate',
        'apply_timbre_fiscal',
        'timbre_fiscal_rate',
        'timbre_fiscal_max',
        'default_payment_terms',
        'payment_reminder_days',
        'email_from_name',
        'email_from_address',
        'auto_send_invoices',
        'auto_send_quotes',
        'track_stock',
        'allow_negative_stock',
        'low_stock_alert_days',
        'auto_convert_won_opportunities',
        'opportunity_stale_days',
        'enable_2fa',
        'session_timeout',
        'password_expiry_days',
        'enabled_notifications',
        'custom_settings',
    ];

    protected $casts = [
        'apply_timbre_fiscal' => 'boolean',
        'auto_send_invoices' => 'boolean',
        'auto_send_quotes' => 'boolean',
        'track_stock' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'auto_convert_won_opportunities' => 'boolean',
        'enable_2fa' => 'boolean',
        'enabled_notifications' => 'array',
        'custom_settings' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get or create settings for tenant.
     */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(['tenant_id' => $tenantId]);
    }
}
