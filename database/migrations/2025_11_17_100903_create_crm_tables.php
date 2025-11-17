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
        // Contacts/Leads
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable(); // Poste
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->enum('type', ['lead', 'prospect', 'customer'])->default('lead');
            $table->enum('source', ['website', 'referral', 'cold_call', 'social_media', 'event', 'other'])->nullable();
            $table->integer('score')->default(0); // Score de qualification
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Commercial assigné
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null'); // Lien avec client si converti
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index('assigned_to');
        });

        // Pipelines commerciaux
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Étapes du pipeline
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('probability')->default(0); // Probabilité de conversion (0-100%)
            $table->integer('order')->default(0);
            $table->string('color')->default('#3b82f6'); // Couleur pour l'affichage
            $table->timestamps();

            $table->index('pipeline_id');
        });

        // Opportunités commerciales
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pipeline_id')->constrained()->onDelete('cascade');
            $table->foreignId('stage_id')->constrained('pipeline_stages')->onDelete('cascade');
            $table->decimal('value', 12, 3)->default(0); // Valeur estimée
            $table->integer('probability')->default(0); // Probabilité de succès
            $table->date('expected_close_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->enum('status', ['open', 'won', 'lost'])->default('open');
            $table->string('lost_reason')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index('assigned_to');
            $table->index('expected_close_date');
        });

        // Activités commerciales (appels, emails, rendez-vous)
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['call', 'email', 'meeting', 'task', 'note', 'visit']); // Type d'activité
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('opportunity_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Utilisateur responsable
            $table->datetime('scheduled_at')->nullable(); // Date/heure prévue
            $table->datetime('completed_at')->nullable(); // Date/heure de réalisation
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->integer('duration')->nullable(); // Durée en minutes
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->text('result')->nullable(); // Compte-rendu
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type', 'status']);
            $table->index('user_id');
            $table->index('scheduled_at');
        });

        // Tournées commerciales
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Commercial
            $table->date('tour_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index('tour_date');
        });

        // Visites/Points de tournée
        Schema::create('tour_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name'); // Nom du point de visite
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->time('planned_time')->nullable();
            $table->datetime('check_in_at')->nullable(); // Check-in GPS
            $table->datetime('check_out_at')->nullable(); // Check-out GPS
            $table->text('notes')->nullable(); // Compte-rendu de visite
            $table->enum('status', ['pending', 'visited', 'skipped'])->default('pending');
            $table->integer('order')->default(0); // Ordre de visite
            $table->timestamps();

            $table->index('tour_id');
        });

        // Objectifs commerciaux
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Commercial (null = équipe complète)
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('target_amount', 12, 3)->default(0); // Objectif CA
            $table->integer('target_deals')->default(0); // Objectif nombre d'affaires
            $table->decimal('achieved_amount', 12, 3)->default(0); // CA réalisé
            $table->integer('achieved_deals')->default(0); // Nombre d'affaires réalisées
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['start_date', 'end_date']);
        });

        // Tags/Étiquettes
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#3b82f6');
            $table->enum('type', ['contact', 'opportunity', 'product', 'general'])->default('general');
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
        });

        // Table pivot pour les tags
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->morphs('taggable'); // Polymorphic relation
            $table->timestamps();

            $table->index(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('tour_visits');
        Schema::dropIfExists('tours');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('pipelines');
        Schema::dropIfExists('contacts');
    }
};
