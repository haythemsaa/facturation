<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Opportunity;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Position;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Subscription;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding database with test data...');

        // Créer les permissions et rôles
        $this->createRolesAndPermissions();

        // Créer 3 tenants avec différents plans
        $plans = ['starter', 'business', 'enterprise'];

        foreach ($plans as $index => $plan) {
            $this->command->info("Creating tenant with {$plan} plan...");

            $tenant = Tenant::factory()->create([
                'name' => ucfirst($plan) . ' Company ' . ($index + 1),
            ]);

            // Créer subscription active
            $modules = [
                'starter' => ['stock'],
                'business' => ['stock', 'crm'],
                'enterprise' => ['stock', 'crm', 'hr'],
            ];

            Subscription::factory()->create([
                'tenant_id' => $tenant->id,
                'plan' => $plan,
                'modules' => $modules[$plan],
                'status' => 'active',
            ]);

            // Créer utilisateurs pour ce tenant
            $admin = User::factory()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Admin ' . $tenant->name,
                'email' => "admin@{$plan}.tunisbusiness.tn",
            ]);
            $admin->assignRole('admin');

            // Créer 4 utilisateurs supplémentaires si le plan le permet
            if ($plan !== 'starter' || $index === 0) {
                for ($i = 1; $i <= 4; $i++) {
                    $user = User::factory()->create([
                        'tenant_id' => $tenant->id,
                    ]);
                    $user->assignRole('user');
                }
            }

            // Seeder pour le module Stock (tous les plans)
            $this->seedStockModule($tenant, $admin);

            // Seeder pour le module CRM (business et enterprise)
            if (in_array('crm', $modules[$plan])) {
                $this->seedCRMModule($tenant, $admin);
            }

            // Seeder pour le module RH (enterprise uniquement)
            if (in_array('hr', $modules[$plan])) {
                $this->seedHRModule($tenant, $admin);
            }
        }

        $this->command->info('✅ Database seeded successfully!');
    }

    private function createRolesAndPermissions(): void
    {
        $this->command->info('Creating roles and permissions...');

        // Créer les rôles
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'manager']);
        $user = Role::create(['name' => 'user']);

        // Créer quelques permissions de base
        $permissions = [
            'view_stock', 'create_stock', 'edit_stock', 'delete_stock',
            'view_crm', 'create_crm', 'edit_crm', 'delete_crm',
            'view_hr', 'create_hr', 'edit_hr', 'delete_hr',
            'manage_settings', 'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assigner permissions aux rôles
        $admin->givePermissionTo(Permission::all());
        $manager->givePermissionTo([
            'view_stock', 'create_stock', 'edit_stock',
            'view_crm', 'create_crm', 'edit_crm',
            'view_hr', 'create_hr', 'edit_hr',
        ]);
        $user->givePermissionTo([
            'view_stock', 'create_stock',
            'view_crm', 'create_crm',
            'view_hr',
        ]);
    }

    private function seedStockModule(Tenant $tenant, User $admin): void
    {
        $this->command->info("  📦 Seeding Stock module for {$tenant->name}...");

        // Créer catégories
        $categories = Category::factory(8)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Créer produits
        $products = Product::factory(50)->create([
            'tenant_id' => $tenant->id,
            'category_id' => fn() => $categories->random()->id,
        ]);

        // Créer entrepôts
        $warehouses = Warehouse::factory(3)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Créer stock pour les produits
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                Stock::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => fake()->numberBetween(0, 500),
                    'reserved_quantity' => fake()->numberBetween(0, 50),
                ]);
            }
        }

        // Créer clients
        $customers = Customer::factory(30)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Créer fournisseurs
        $suppliers = Supplier::factory(15)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Créer documents (factures, devis, etc.)
        foreach ($customers->random(10) as $customer) {
            Document::factory(fake()->numberBetween(1, 5))->create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'warehouse_id' => $warehouses->random()->id,
                'user_id' => $admin->id,
            ]);
        }
    }

    private function seedCRMModule(Tenant $tenant, User $admin): void
    {
        $this->command->info("  👥 Seeding CRM module for {$tenant->name}...");

        // Créer pipeline
        $pipeline = Pipeline::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Ventes B2B',
        ]);

        // Créer étapes du pipeline
        $stages = [
            ['name' => 'Nouveau', 'order' => 1, 'probability' => 10],
            ['name' => 'Qualifié', 'order' => 2, 'probability' => 25],
            ['name' => 'Proposition', 'order' => 3, 'probability' => 50],
            ['name' => 'Négociation', 'order' => 4, 'probability' => 75],
            ['name' => 'Gagné', 'order' => 5, 'probability' => 100],
        ];

        foreach ($stages as $stageData) {
            PipelineStage::create([
                'tenant_id' => $tenant->id,
                'pipeline_id' => $pipeline->id,
                ...$stageData,
            ]);
        }

        // Créer contacts
        $contacts = Contact::factory(40)->create([
            'tenant_id' => $tenant->id,
            'assigned_to_id' => $admin->id,
        ]);

        // Créer opportunités
        $pipelineStages = $pipeline->stages;
        foreach ($contacts->random(20) as $contact) {
            $opportunity = Opportunity::factory()->create([
                'tenant_id' => $tenant->id,
                'contact_id' => $contact->id,
                'pipeline_id' => $pipeline->id,
                'pipeline_stage_id' => $pipelineStages->random()->id,
                'assigned_to_id' => $admin->id,
            ]);

            // Créer activités pour cette opportunité
            Activity::factory(fake()->numberBetween(2, 5))->create([
                'tenant_id' => $tenant->id,
                'contact_id' => $contact->id,
                'opportunity_id' => $opportunity->id,
                'user_id' => $admin->id,
            ]);
        }
    }

    private function seedHRModule(Tenant $tenant, User $admin): void
    {
        $this->command->info("  💼 Seeding HR module for {$tenant->name}...");

        // Créer départements
        $departments = Department::factory(5)->create([
            'tenant_id' => $tenant->id,
        ]);

        // Créer postes
        $positions = Position::factory(8)->create([
            'tenant_id' => $tenant->id,
            'department_id' => fn() => $departments->random()->id,
        ]);

        // Créer employés
        $employees = Employee::factory(20)->create([
            'tenant_id' => $tenant->id,
            'department_id' => fn() => $departments->random()->id,
            'position_id' => fn() => $positions->random()->id,
        ]);

        // Créer contrats pour chaque employé
        foreach ($employees as $employee) {
            Contract::factory()->create([
                'tenant_id' => $tenant->id,
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
            ]);
        }
    }
}
