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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de l'entreprise
            $table->string('slug')->unique(); // Slug unique pour sous-domaine
            $table->string('matricule_fiscal')->unique()->nullable(); // Matricule fiscal tunisien
            $table->string('code_tva')->nullable(); // Code TVA
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Tunisie');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Paramètres personnalisés
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
