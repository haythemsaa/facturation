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
        // Catégories d'articles
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Articles/Produits
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->unique(); // Code article unique
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['product', 'service'])->default('product');
            $table->enum('unit', ['piece', 'kg', 'liter', 'meter', 'box', 'pack'])->default('piece');
            $table->decimal('purchase_price', 12, 3)->default(0);
            $table->decimal('selling_price', 12, 3)->default(0);
            $table->decimal('minimum_price', 12, 3)->nullable(); // Prix minimum de vente
            $table->enum('tva_rate', ['19', '13', '7', '0'])->default('19'); // Taux TVA tunisien
            $table->decimal('stock_alert_threshold', 12, 3)->default(0); // Seuil d'alerte
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
            $table->index('code');
        });

        // Dépôts/Magasins
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Stock par dépôt
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('reserved_quantity', 12, 3)->default(0); // Quantité réservée
            $table->decimal('available_quantity', 12, 3)->default(0); // Quantité disponible
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
            $table->index('tenant_id');
        });

        // Mouvements de stock
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment', 'return']); // Type de mouvement
            $table->decimal('quantity', 12, 3);
            $table->decimal('cost', 12, 3)->nullable(); // Coût unitaire
            $table->string('reference')->nullable(); // Référence document
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->date('movement_date');
            $table->timestamps();

            $table->index(['tenant_id', 'product_id']);
            $table->index('movement_date');
        });

        // Clients
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique();
            $table->enum('type', ['individual', 'company'])->default('individual');
            $table->string('name');
            $table->string('contact_name')->nullable(); // Nom du contact si entreprise
            $table->string('matricule_fiscal')->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('credit_limit', 12, 3)->default(0); // Plafond de crédit
            $table->integer('payment_terms')->default(0); // Délai de paiement (jours)
            $table->decimal('discount_rate', 5, 2)->default(0); // Remise par défaut
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Fournisseurs
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('matricule_fiscal')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->integer('payment_terms')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Documents commerciaux (Devis, BL, Factures, Avoirs)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['quote', 'delivery_note', 'invoice', 'credit_note', 'purchase_order', 'purchase_invoice']);
            $table->string('number')->unique(); // Numéro séquentiel unique
            $table->date('date');
            $table->date('due_date')->nullable(); // Date d'échéance
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('subtotal', 12, 3)->default(0); // Sous-total HT
            $table->decimal('discount_amount', 12, 3)->default(0); // Montant remise
            $table->decimal('discount_rate', 5, 2)->default(0); // Taux remise
            $table->decimal('total_ht', 12, 3)->default(0); // Total HT
            $table->decimal('total_tva', 12, 3)->default(0); // Total TVA
            $table->decimal('timbre_fiscal', 12, 3)->default(0); // Timbre fiscal (max 1 TND)
            $table->decimal('total_ttc', 12, 3)->default(0); // Total TTC
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'paid', 'partially_paid', 'cancelled'])->default('draft');
            $table->text('note')->nullable(); // Note/Observations
            $table->text('terms')->nullable(); // Conditions
            $table->foreignId('related_document_id')->nullable()->constrained('documents')->onDelete('set null'); // Document lié (ex: facture liée au devis)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Créé par
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type', 'status']);
            $table->index('number');
            $table->index('date');
        });

        // Lignes de documents
        Schema::create('document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 3);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('discount_amount', 12, 3)->default(0);
            $table->decimal('tva_rate', 5, 2); // Taux TVA
            $table->decimal('tva_amount', 12, 3); // Montant TVA
            $table->decimal('total_ht', 12, 3); // Total ligne HT
            $table->decimal('total_ttc', 12, 3); // Total ligne TTC
            $table->integer('line_order')->default(0); // Ordre d'affichage
            $table->timestamps();

            $table->index('document_id');
        });

        // Paiements
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 12, 3);
            $table->enum('method', ['cash', 'check', 'bank_transfer', 'credit_card', 'other'])->default('cash');
            $table->string('reference')->nullable(); // Numéro de chèque, virement, etc.
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'document_id']);
        });

        // Inventaires
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            $table->index(['tenant_id', 'warehouse_id']);
        });

        // Lignes d'inventaire
        Schema::create('inventory_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('theoretical_quantity', 12, 3)->default(0); // Qté théorique
            $table->decimal('counted_quantity', 12, 3)->default(0); // Qté comptée
            $table->decimal('difference', 12, 3)->default(0); // Écart
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('inventory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_lines');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('document_lines');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
