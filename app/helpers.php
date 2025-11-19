<?php

/**
 * TunisBusiness Suite - Helper Functions
 *
 * Global helper functions available throughout the application.
 */

if (!function_exists('format_currency')) {
    /**
     * Format amount as Tunisian currency.
     *
     * @param float $amount
     * @param int $decimals
     * @return string
     */
    function format_currency(float $amount, int $decimals = 3): string
    {
        return number_format($amount, $decimals, ',', ' ') . ' TND';
    }
}

if (!function_exists('format_percentage')) {
    /**
     * Format number as percentage.
     *
     * @param float $value
     * @param int $decimals
     * @return string
     */
    function format_percentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', ' ') . '%';
    }
}

if (!function_exists('calculate_tva')) {
    /**
     * Calculate TVA amount.
     *
     * @param float $amountHT
     * @param float $tvaRate
     * @return float
     */
    function calculate_tva(float $amountHT, float $tvaRate): float
    {
        return round($amountHT * ($tvaRate / 100), 3);
    }
}

if (!function_exists('calculate_timbre_fiscal')) {
    /**
     * Calculate timbre fiscal (1% max 1 TND).
     *
     * @param float $amountTTC
     * @return float
     */
    function calculate_timbre_fiscal(float $amountTTC): float
    {
        $timbre = round($amountTTC * 0.01, 3);
        return min($timbre, 1.000);
    }
}

if (!function_exists('tenant_id')) {
    /**
     * Get current user's tenant ID.
     *
     * @return int|null
     */
    function tenant_id(): ?int
    {
        return auth()->user()?->tenant_id;
    }
}

if (!function_exists('current_tenant')) {
    /**
     * Get current user's tenant.
     *
     * @return \App\Models\Tenant|null
     */
    function current_tenant(): ?\App\Models\Tenant
    {
        return auth()->user()?->tenant;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if current user is admin.
     *
     * @return bool
     */
    function is_admin(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }
}

if (!function_exists('has_module')) {
    /**
     * Check if current tenant has access to a module.
     *
     * @param string $module
     * @return bool
     */
    function has_module(string $module): bool
    {
        $tenant = current_tenant();
        if (!$tenant || !$tenant->subscription) {
            return false;
        }

        return in_array($module, $tenant->subscription->modules);
    }
}

if (!function_exists('format_phone')) {
    /**
     * Format Tunisian phone number.
     *
     * @param string $phone
     * @return string
     */
    function format_phone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Format as +216 XX XXX XXX
        if (strlen($phone) === 8) {
            return '+216 ' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 3) . ' ' . substr($phone, 5);
        }

        return $phone;
    }
}

if (!function_exists('format_matricule_fiscal')) {
    /**
     * Format Tunisian tax ID (Matricule Fiscal).
     *
     * @param string $mf
     * @return string
     */
    function format_matricule_fiscal(string $mf): string
    {
        // Remove all non-alphanumeric characters
        $mf = preg_replace('/[^A-Z0-9]/', '', strtoupper($mf));

        // Format as XXXXXXX/X/X/X/XXX
        if (strlen($mf) === 13 || strlen($mf) === 14) {
            return substr($mf, 0, 7) . '/' .
                   substr($mf, 7, 1) . '/' .
                   substr($mf, 8, 1) . '/' .
                   substr($mf, 9, 1) . '/' .
                   substr($mf, 10);
        }

        return $mf;
    }
}

if (!function_exists('fiscal_year')) {
    /**
     * Get current fiscal year.
     *
     * @param \DateTime|null $date
     * @return int
     */
    function fiscal_year(?\DateTime $date = null): int
    {
        $date = $date ?? now();
        return (int) $date->format('Y');
    }
}

if (!function_exists('working_days')) {
    /**
     * Calculate working days between two dates (excluding weekends).
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    function working_days(\DateTime $start, \DateTime $end): int
    {
        $days = 0;
        $current = clone $start;

        while ($current <= $end) {
            $dayOfWeek = $current->format('N');
            if ($dayOfWeek < 6) { // Monday = 1, Sunday = 7
                $days++;
            }
            $current->modify('+1 day');
        }

        return $days;
    }
}

if (!function_exists('cnss_employee')) {
    /**
     * Calculate CNSS employee contribution (9.18%).
     *
     * @param float $baseSalary
     * @return float
     */
    function cnss_employee(float $baseSalary): float
    {
        return round($baseSalary * 0.0918, 3);
    }
}

if (!function_exists('cnss_employer')) {
    /**
     * Calculate CNSS employer contribution (16.57%).
     *
     * @param float $baseSalary
     * @return float
     */
    function cnss_employer(float $baseSalary): float
    {
        return round($baseSalary * 0.1657, 3);
    }
}

if (!function_exists('calculate_irpp_2025')) {
    /**
     * Calculate IRPP 2025 with progressive rates.
     *
     * @param float $annualIncome
     * @param bool $isFamilyHead
     * @param int $childrenCount
     * @return float
     */
    function calculate_irpp_2025(float $annualIncome, bool $isFamilyHead = false, int $childrenCount = 0): float
    {
        // Deductions
        $deduction = 0;
        if ($isFamilyHead) {
            $deduction += 300; // Chef de famille
        }
        $deduction += min($childrenCount, 4) * 100; // Max 4 children

        $taxableIncome = max(0, $annualIncome - $deduction);

        // Progressive rates
        $tax = 0;
        if ($taxableIncome > 50000) {
            $tax += ($taxableIncome - 50000) * 0.35;
            $taxableIncome = 50000;
        }
        if ($taxableIncome > 30000) {
            $tax += ($taxableIncome - 30000) * 0.32;
            $taxableIncome = 30000;
        }
        if ($taxableIncome > 20000) {
            $tax += ($taxableIncome - 20000) * 0.28;
            $taxableIncome = 20000;
        }
        if ($taxableIncome > 5000) {
            $tax += ($taxableIncome - 5000) * 0.26;
        }

        return round($tax, 3);
    }
}

if (!function_exists('generate_document_number')) {
    /**
     * Generate document number with prefix and auto-increment.
     *
     * @param string $type
     * @param int $nextNumber
     * @param int $year
     * @return string
     */
    function generate_document_number(string $type, int $nextNumber, int $year): string
    {
        $prefixes = [
            'invoice' => 'FAC',
            'quote' => 'DEV',
            'delivery_note' => 'BL',
            'credit_note' => 'AV',
            'purchase_order' => 'BC',
        ];

        $prefix = $prefixes[$type] ?? 'DOC';

        return sprintf('%s-%d-%05d', $prefix, $year, $nextNumber);
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename for safe storage.
     *
     * @param string $filename
     * @return string
     */
    function sanitize_filename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Trim underscores from ends
        return trim($filename, '_');
    }
}

if (!function_exists('log_activity')) {
    /**
     * Log user activity (wrapper for audit log).
     *
     * @param string $event
     * @param mixed $model
     * @param array $data
     * @return void
     */
    function log_activity(string $event, $model, array $data = []): void
    {
        if (!$model) {
            return;
        }

        \App\Models\AuditLog::create([
            'tenant_id' => tenant_id(),
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values' => $data['old'] ?? null,
            'new_values' => $data['new'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}

if (!function_exists('send_notification')) {
    /**
     * Send notification to user (wrapper for NotificationService).
     *
     * @param \App\Models\User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param string|null $actionUrl
     * @return void
     */
    function send_notification(
        \App\Models\User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): void {
        app(\App\Services\NotificationService::class)->create(
            user: $user,
            type: $type,
            title: $title,
            message: $message,
            actionUrl: $actionUrl
        );
    }
}

if (!function_exists('active_subscription')) {
    /**
     * Check if tenant has active subscription.
     *
     * @return bool
     */
    function active_subscription(): bool
    {
        $tenant = current_tenant();
        return $tenant && $tenant->subscription && $tenant->subscription->status === 'active';
    }
}

if (!function_exists('subscription_expires_in')) {
    /**
     * Get days until subscription expires.
     *
     * @return int|null
     */
    function subscription_expires_in(): ?int
    {
        $tenant = current_tenant();
        if (!$tenant || !$tenant->subscription || !$tenant->subscription->ends_at) {
            return null;
        }

        return now()->diffInDays($tenant->subscription->ends_at, false);
    }
}
