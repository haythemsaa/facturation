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
        // Départements
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Postes/Fonctions
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title'); // Intitulé du poste
            $table->text('description')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('min_salary', 10, 3)->nullable();
            $table->decimal('max_salary', 10, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // Employés
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Lien avec user si accès plateforme
            $table->string('employee_number')->unique(); // Matricule
            $table->string('first_name');
            $table->string('last_name');
            $table->string('cin')->unique(); // Carte d'identité nationale
            $table->string('cnss_number')->nullable(); // Numéro CNSS
            $table->date('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->integer('children_count')->default(0); // Nombre d'enfants à charge
            $table->boolean('is_family_head')->default(false); // Chef de famille (IRPP)
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('employees')->onDelete('set null'); // Manager direct
            $table->date('hire_date');
            $table->date('end_date')->nullable(); // Date de fin de contrat
            $table->enum('status', ['active', 'on_leave', 'suspended', 'terminated'])->default('active');
            $table->string('photo')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index('employee_number');
        });

        // Contrats de travail
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['cdi', 'cdd', 'sivp', 'karama', 'internship', 'other']); // Types de contrats tunisiens
            $table->string('reference')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Pour CDD
            $table->decimal('base_salary', 10, 3); // Salaire de base
            $table->integer('working_hours_per_week')->default(40); // 40h légal en Tunisie
            $table->integer('vacation_days_per_year')->default(12); // 1 jour/mois légal
            $table->text('terms')->nullable(); // Clauses spécifiques
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
        });

        // Avenants aux contrats
        Schema::create('contract_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->date('effective_date');
            $table->string('type'); // Type de modification (salaire, poste, etc.)
            $table->text('description');
            $table->json('changes')->nullable(); // Détails des changements
            $table->timestamps();

            $table->index('contract_id');
        });

        // Pointages
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('worked_minutes')->default(0); // Minutes travaillées
            $table->integer('overtime_minutes')->default(0); // Heures supplémentaires
            $table->integer('late_minutes')->default(0); // Retard
            $table->enum('status', ['present', 'absent', 'half_day', 'leave', 'public_holiday'])->default('present');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['tenant_id', 'date']);
        });

        // Types de congés
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Congé annuel, maladie, maternité, etc.
            $table->text('description')->nullable();
            $table->integer('default_days')->default(0); // Nombre de jours par défaut
            $table->boolean('is_paid')->default(true); // Payé ou non payé
            $table->boolean('requires_approval')->default(true);
            $table->boolean('requires_document')->default(false); // Certificat médical, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // Demandes de congés
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 5, 1); // Nombre de jours (peut être 0.5 pour demi-journée)
            $table->text('reason')->nullable();
            $table->string('document')->nullable(); // Pièce jointe
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'employee_id', 'status']);
        });

        // Soldes de congés
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->decimal('total_days', 5, 1)->default(0); // Total annuel
            $table->decimal('used_days', 5, 1)->default(0); // Utilisé
            $table->decimal('remaining_days', 5, 1)->default(0); // Restant
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        // Éléments de paie (primes, déductions)
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique(); // Code de l'élément
            $table->enum('type', ['earning', 'deduction']); // Gain ou déduction
            $table->enum('category', ['base', 'allowance', 'bonus', 'overtime', 'deduction', 'tax', 'social']); // Catégorie
            $table->boolean('is_taxable')->default(true); // Soumis à IRPP
            $table->boolean('is_cnss_subject')->default(true); // Soumis à CNSS
            $table->decimal('default_amount', 10, 3)->nullable();
            $table->boolean('is_fixed')->default(true); // Montant fixe ou calculé
            $table->string('calculation_formula')->nullable(); // Formule de calcul si applicable
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        // Bulletins de paie
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->integer('month'); // Mois (1-12)
            $table->integer('year'); // Année
            $table->date('payment_date')->nullable();
            $table->decimal('base_salary', 10, 3)->default(0);
            $table->decimal('gross_salary', 10, 3)->default(0); // Salaire brut
            $table->decimal('total_earnings', 10, 3)->default(0); // Total gains
            $table->decimal('total_deductions', 10, 3)->default(0); // Total déductions
            $table->decimal('cnss_employee', 10, 3)->default(0); // Cotisation CNSS salarié (9.18%)
            $table->decimal('cnss_employer', 10, 3)->default(0); // Cotisation CNSS employeur (16.57%)
            $table->decimal('irpp', 10, 3)->default(0); // IRPP retenu à la source
            $table->decimal('css', 10, 3)->default(0); // Contribution Sociale de Solidarité (1%)
            $table->decimal('tfp', 10, 3)->default(0); // Taxe de Formation Professionnelle (1%)
            $table->decimal('foprolos', 10, 3)->default(0); // FOPROLOS (1%)
            $table->decimal('net_salary', 10, 3)->default(0); // Salaire net à payer
            $table->integer('worked_days')->default(0);
            $table->decimal('worked_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->enum('status', ['draft', 'validated', 'paid'])->default('draft');
            $table->json('details')->nullable(); // Détails ligne par ligne
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['tenant_id', 'year', 'month']);
        });

        // Lignes de bulletin de paie
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_item_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1); // Quantité (heures, jours, etc.)
            $table->decimal('rate', 10, 3)->default(0); // Taux unitaire
            $table->decimal('amount', 10, 3)->default(0); // Montant total
            $table->timestamps();

            $table->index('payslip_id');
        });

        // Déclarations CNSS
        Schema::create('cnss_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('total_salaries', 12, 3)->default(0); // Masse salariale
            $table->decimal('total_employee_contributions', 12, 3)->default(0); // Total cotisations salariales
            $table->decimal('total_employer_contributions', 12, 3)->default(0); // Total cotisations patronales
            $table->decimal('total_amount', 12, 3)->default(0); // Total à payer
            $table->date('declaration_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'paid'])->default('draft');
            $table->string('reference')->nullable(); // Référence de paiement
            $table->json('details')->nullable(); // Détails par employé
            $table->timestamps();

            $table->unique(['tenant_id', 'month', 'year']);
        });

        // Déclarations IRPP
        Schema::create('irpp_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('total_gross_salaries', 12, 3)->default(0);
            $table->decimal('total_irpp_withheld', 12, 3)->default(0); // Total IRPP retenu
            $table->date('declaration_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'paid'])->default('draft');
            $table->string('reference')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'month', 'year']);
        });

        // Documents RH
        Schema::create('hr_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['contract', 'amendment', 'certificate', 'warning', 'medical', 'other']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'employee_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_documents');
        Schema::dropIfExists('irpp_declarations');
        Schema::dropIfExists('cnss_declarations');
        Schema::dropIfExists('payslip_lines');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('contract_amendments');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
