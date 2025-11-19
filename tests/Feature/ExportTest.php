<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    /**
     * Test user can export document as PDF.
     */
    public function test_user_can_export_document_as_pdf(): void
    {
        Sanctum::actingAs($this->user);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $document = Document::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'invoice',
            'status' => 'validated',
        ]);

        $response = $this->getJson("/api/export/document/{$document->id}/pdf");

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Test guest cannot export documents.
     */
    public function test_guest_cannot_export_documents(): void
    {
        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $document = Document::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $response = $this->getJson("/api/export/document/{$document->id}/pdf");

        $response->assertStatus(401);
    }

    /**
     * Test user can export sales report as Excel.
     */
    public function test_user_can_export_sales_excel(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/export/sales/excel?start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    /**
     * Test user can export stock report as Excel.
     */
    public function test_user_can_export_stock_excel(): void
    {
        Sanctum::actingAs($this->user);

        // Create some stock data
        $product = Product::factory()->create(['tenant_id' => $this->tenant->id]);

        $response = $this->getJson('/api/export/stock/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    /**
     * Test user can export contacts as Excel.
     */
    public function test_user_can_export_contacts_excel(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/export/contacts/excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('spreadsheet', $response->headers->get('content-type'));
    }

    /**
     * Test bulk PDF export with multiple documents.
     */
    public function test_user_can_export_bulk_pdf(): void
    {
        Sanctum::actingAs($this->user);

        $customer = Customer::factory()->create(['tenant_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $this->tenant->id]);

        $documents = Document::factory(3)->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'invoice',
            'status' => 'validated',
        ]);

        $response = $this->postJson('/api/export/bulk-pdf', [
            'document_ids' => $documents->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * Test export validation with invalid date range.
     */
    public function test_export_fails_with_invalid_date_range(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/export/sales/excel?start_date=invalid&end_date=2025-01-31');

        $response->assertStatus(422);
    }

    /**
     * Test user cannot export documents from other tenants.
     */
    public function test_user_cannot_export_other_tenant_documents(): void
    {
        Sanctum::actingAs($this->user);

        $otherTenant = Tenant::factory()->create();
        $customer = Customer::factory()->create(['tenant_id' => $otherTenant->id]);
        $warehouse = Warehouse::factory()->create(['tenant_id' => $otherTenant->id]);

        $document = Document::factory()->create([
            'tenant_id' => $otherTenant->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $response = $this->getJson("/api/export/document/{$document->id}/pdf");

        $response->assertStatus(403);
    }
}
