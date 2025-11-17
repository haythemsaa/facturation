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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // "Template Moderne", "Template Classic"
            $table->enum('type', ['invoice', 'quote', 'delivery_note', 'credit_note', 'purchase_order'])->default('invoice');
            $table->text('header_html')->nullable(); // HTML header personnalisé
            $table->text('footer_html')->nullable(); // HTML footer personnalisé
            $table->text('body_html')->nullable(); // HTML body template (avec variables)
            $table->json('styles')->nullable(); // CSS/Styles personnalisés
            $table->json('settings')->nullable(); // Paramètres (logo position, colors, fonts)
            $table->boolean('is_default')->default(false); // Template par défaut
            $table->boolean('is_active')->default(true);
            $table->string('preview_image')->nullable(); // Screenshot du template
            $table->timestamps();

            $table->index(['tenant_id', 'type', 'is_default']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
