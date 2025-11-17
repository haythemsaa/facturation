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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Qui
            $table->string('event'); // created, updated, deleted, restored, viewed, etc.
            $table->morphs('auditable'); // Quoi (Model)
            $table->text('old_values')->nullable(); // JSON - Valeurs avant
            $table->text('new_values')->nullable(); // JSON - Valeurs après
            $table->string('ip_address', 45)->nullable(); // D'où
            $table->string('user_agent')->nullable(); // Navigateur/Device
            $table->text('url')->nullable(); // Endpoint appelé
            $table->text('description')->nullable(); // Description humaine
            $table->json('metadata')->nullable(); // Données additionnelles
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['event']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
