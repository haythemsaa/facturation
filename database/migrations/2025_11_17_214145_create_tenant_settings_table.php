<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->onDelete('cascade');

            // General Settings
            $table->string('timezone')->default('Africa/Tunis');
            $table->string('date_format')->default('d/m/Y');
            $table->string('time_format')->default('H:i');
            $table->string('currency')->default('TND');
            $table->string('language')->default('fr');

            // Invoice Settings
            $table->string('invoice_prefix')->default('INV');
            $table->string('quote_prefix')->default('QTE');
            $table->integer('invoice_next_number')->default(1);
            $table->integer('quote_next_number')->default(1);
            $table->text('invoice_terms')->nullable(); // CGV
            $table->text('invoice_footer')->nullable();

            // Tax Settings
            $table->decimal('default_tva_rate', 5, 2)->default(19.00);
            $table->boolean('apply_timbre_fiscal')->default(true);
            $table->decimal('timbre_fiscal_rate', 5, 2)->default(1.00);
            $table->decimal('timbre_fiscal_max', 8, 3)->default(1.000);

            // Payment Settings
            $table->enum('default_payment_terms', ['immediate', 'net_15', 'net_30', 'net_60', 'net_90'])->default('net_30');
            $table->integer('payment_reminder_days')->default(7); // Rappel après X jours

            // Email Settings
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->boolean('auto_send_invoices')->default(false);
            $table->boolean('auto_send_quotes')->default(false);

            // Stock Settings
            $table->boolean('track_stock')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->integer('low_stock_alert_days')->default(7);

            // CRM Settings
            $table->boolean('auto_convert_won_opportunities')->default(true);
            $table->integer('opportunity_stale_days')->default(30);

            // Security Settings
            $table->boolean('enable_2fa')->default(false);
            $table->integer('session_timeout')->default(120); // minutes
            $table->integer('password_expiry_days')->default(90);

            // Notification Settings
            $table->json('enabled_notifications')->nullable();

            // Custom Settings (JSON for flexibility)
            $table->json('custom_settings')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
