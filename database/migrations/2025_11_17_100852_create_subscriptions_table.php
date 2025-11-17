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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('plan', ['starter', 'business', 'enterprise', 'custom'])->default('starter');
            $table->json('modules')->nullable(); // Modules actifs: ['stock', 'crm', 'hr']
            $table->integer('max_users')->default(3);
            $table->integer('max_documents_per_month')->default(500);
            $table->decimal('price', 10, 3)->default(49.000); // Prix en TND
            $table->enum('status', ['active', 'suspended', 'cancelled', 'trial'])->default('trial');
            $table->date('trial_ends_at')->nullable();
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->date('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
