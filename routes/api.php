<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Stock\ProductController;
use App\Http\Controllers\Api\Stock\CustomerController;
use App\Http\Controllers\Api\Stock\SupplierController;
use App\Http\Controllers\Api\Stock\DocumentController;
use App\Http\Controllers\Api\Stock\StockController;
use App\Http\Controllers\Api\Stock\WarehouseController;
use App\Http\Controllers\Api\CRM\ContactController;
use App\Http\Controllers\Api\CRM\OpportunityController;
use App\Http\Controllers\Api\CRM\ActivityController;
use App\Http\Controllers\Api\CRM\PipelineController;
use App\Http\Controllers\Api\CRM\TourController;
use App\Http\Controllers\Api\HR\EmployeeController;
use App\Http\Controllers\Api\HR\PayslipController;
use App\Http\Controllers\Api\HR\AttendanceController;
use App\Http\Controllers\Api\HR\LeaveRequestController;
use App\Http\Controllers\Api\HR\DeclarationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ============================================
    // MODULE STOCK & FACTURATION
    // ============================================
    Route::prefix('stock')->name('stock.')->group(function () {

        // Products
        Route::apiResource('products', ProductController::class);
        Route::get('products/{product}/stock', [ProductController::class, 'stockDetails']);

        // Categories
        Route::apiResource('categories', \App\Http\Controllers\Api\Stock\CategoryController::class);

        // Warehouses
        Route::apiResource('warehouses', WarehouseController::class);
        Route::get('warehouses/{warehouse}/stock', [WarehouseController::class, 'stockList']);

        // Stock Management
        Route::get('stocks', [StockController::class, 'index']);
        Route::post('stocks/movement', [StockController::class, 'createMovement']);
        Route::get('stocks/movements', [StockController::class, 'movements']);
        Route::post('stocks/transfer', [StockController::class, 'transfer']);
        Route::post('stocks/adjustment', [StockController::class, 'adjustment']);

        // Customers
        Route::apiResource('customers', CustomerController::class);
        Route::get('customers/{customer}/documents', [CustomerController::class, 'documents']);
        Route::get('customers/{customer}/balance', [CustomerController::class, 'balance']);

        // Suppliers
        Route::apiResource('suppliers', SupplierController::class);
        Route::get('suppliers/{supplier}/documents', [SupplierController::class, 'documents']);

        // Documents (Devis, BL, Factures, Avoirs)
        Route::apiResource('documents', DocumentController::class);
        Route::post('documents/{document}/validate', [DocumentController::class, 'validate']);
        Route::post('documents/{document}/send', [DocumentController::class, 'send']);
        Route::post('documents/{document}/payment', [DocumentController::class, 'addPayment']);
        Route::get('documents/{document}/pdf', [DocumentController::class, 'pdf']);

        // Payments
        Route::get('payments', [\App\Http\Controllers\Api\Stock\PaymentController::class, 'index']);
        Route::post('payments', [\App\Http\Controllers\Api\Stock\PaymentController::class, 'store']);

        // Inventories
        Route::apiResource('inventories', \App\Http\Controllers\Api\Stock\InventoryController::class);
        Route::post('inventories/{inventory}/validate', [\App\Http\Controllers\Api\Stock\InventoryController::class, 'validate']);

        // Reports
        Route::get('reports/stock-valuation', [\App\Http\Controllers\Api\Stock\ReportController::class, 'stockValuation']);
        Route::get('reports/sales', [\App\Http\Controllers\Api\Stock\ReportController::class, 'sales']);
        Route::get('reports/purchases', [\App\Http\Controllers\Api\Stock\ReportController::class, 'purchases']);
        Route::get('reports/tva', [\App\Http\Controllers\Api\Stock\ReportController::class, 'tva']);
    });

    // ============================================
    // MODULE CRM
    // ============================================
    Route::prefix('crm')->name('crm.')->group(function () {

        // Contacts & Leads
        Route::apiResource('contacts', ContactController::class);
        Route::post('contacts/{contact}/convert', [ContactController::class, 'convertToCustomer']);
        Route::post('contacts/{contact}/qualify', [ContactController::class, 'qualify']);

        // Pipelines
        Route::apiResource('pipelines', PipelineController::class);
        Route::post('pipelines/{pipeline}/stages', [PipelineController::class, 'addStage']);
        Route::put('pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'updateStage']);
        Route::delete('pipelines/{pipeline}/stages/{stage}', [PipelineController::class, 'deleteStage']);

        // Opportunities
        Route::apiResource('opportunities', OpportunityController::class);
        Route::post('opportunities/{opportunity}/move', [OpportunityController::class, 'moveStage']);
        Route::post('opportunities/{opportunity}/win', [OpportunityController::class, 'markAsWon']);
        Route::post('opportunities/{opportunity}/lose', [OpportunityController::class, 'markAsLost']);

        // Activities
        Route::apiResource('activities', ActivityController::class);
        Route::post('activities/{activity}/complete', [ActivityController::class, 'complete']);
        Route::get('activities/calendar', [ActivityController::class, 'calendar']);

        // Tours (Tournées commerciales)
        Route::apiResource('tours', TourController::class);
        Route::post('tours/{tour}/start', [TourController::class, 'start']);
        Route::post('tours/{tour}/complete', [TourController::class, 'complete']);
        Route::post('tours/{tour}/visits/{visit}/checkin', [TourController::class, 'checkIn']);
        Route::post('tours/{tour}/visits/{visit}/checkout', [TourController::class, 'checkOut']);

        // Sales Targets
        Route::apiResource('targets', \App\Http\Controllers\Api\CRM\SalesTargetController::class);
        Route::get('targets/progress', [\App\Http\Controllers\Api\CRM\SalesTargetController::class, 'progress']);

        // Tags
        Route::apiResource('tags', \App\Http\Controllers\Api\CRM\TagController::class);

        // Reports
        Route::get('reports/pipeline', [\App\Http\Controllers\Api\CRM\ReportController::class, 'pipeline']);
        Route::get('reports/sales-performance', [\App\Http\Controllers\Api\CRM\ReportController::class, 'salesPerformance']);
        Route::get('reports/conversion', [\App\Http\Controllers\Api\CRM\ReportController::class, 'conversion']);
    });

    // ============================================
    // MODULE RH & PAIE
    // ============================================
    Route::prefix('hr')->name('hr.')->group(function () {

        // Departments
        Route::apiResource('departments', \App\Http\Controllers\Api\HR\DepartmentController::class);

        // Positions
        Route::apiResource('positions', \App\Http\Controllers\Api\HR\PositionController::class);

        // Employees
        Route::apiResource('employees', EmployeeController::class);
        Route::get('employees/{employee}/contracts', [EmployeeController::class, 'contracts']);
        Route::post('employees/{employee}/contracts', [\App\Http\Controllers\Api\HR\ContractController::class, 'store']);

        // Contracts
        Route::apiResource('contracts', \App\Http\Controllers\Api\HR\ContractController::class)->except(['store']);
        Route::post('contracts/{contract}/amendments', [\App\Http\Controllers\Api\HR\ContractController::class, 'addAmendment']);

        // Attendance (Pointages)
        Route::apiResource('attendances', AttendanceController::class);
        Route::post('attendances/checkin', [AttendanceController::class, 'checkIn']);
        Route::post('attendances/checkout', [AttendanceController::class, 'checkOut']);
        Route::get('attendances/summary', [AttendanceController::class, 'summary']);

        // Leave Types
        Route::apiResource('leave-types', \App\Http\Controllers\Api\HR\LeaveTypeController::class);

        // Leave Requests
        Route::apiResource('leave-requests', LeaveRequestController::class);
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);
        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject']);
        Route::get('leave-requests/pending', [LeaveRequestController::class, 'pending']);

        // Leave Balances
        Route::get('leave-balances', [\App\Http\Controllers\Api\HR\LeaveBalanceController::class, 'index']);
        Route::get('leave-balances/{employee}', [\App\Http\Controllers\Api\HR\LeaveBalanceController::class, 'show']);

        // Payroll Items
        Route::apiResource('payroll-items', \App\Http\Controllers\Api\HR\PayrollItemController::class);

        // Payslips
        Route::apiResource('payslips', PayslipController::class);
        Route::post('payslips/{payslip}/validate', [PayslipController::class, 'validate']);
        Route::post('payslips/{payslip}/pay', [PayslipController::class, 'markAsPaid']);
        Route::get('payslips/{payslip}/pdf', [PayslipController::class, 'pdf']);
        Route::post('payslips/generate-batch', [PayslipController::class, 'generateBatch']);

        // CNSS Declarations
        Route::get('declarations/cnss', [DeclarationController::class, 'cnssIndex']);
        Route::post('declarations/cnss', [DeclarationController::class, 'generateCnss']);
        Route::get('declarations/cnss/{declaration}', [DeclarationController::class, 'showCnss']);
        Route::post('declarations/cnss/{declaration}/submit', [DeclarationController::class, 'submitCnss']);
        Route::get('declarations/cnss/{declaration}/export', [DeclarationController::class, 'exportCnss']);

        // IRPP Declarations
        Route::get('declarations/irpp', [DeclarationController::class, 'irppIndex']);
        Route::post('declarations/irpp', [DeclarationController::class, 'generateIrpp']);
        Route::get('declarations/irpp/{declaration}', [DeclarationController::class, 'showIrpp']);
        Route::post('declarations/irpp/{declaration}/submit', [DeclarationController::class, 'submitIrpp']);

        // HR Documents
        Route::get('documents', [\App\Http\Controllers\Api\HR\HrDocumentController::class, 'index']);
        Route::post('documents', [\App\Http\Controllers\Api\HR\HrDocumentController::class, 'store']);
        Route::get('documents/{document}', [\App\Http\Controllers\Api\HR\HrDocumentController::class, 'show']);
        Route::delete('documents/{document}', [\App\Http\Controllers\Api\HR\HrDocumentController::class, 'destroy']);

        // Reports
        Route::get('reports/payroll-summary', [\App\Http\Controllers\Api\HR\ReportController::class, 'payrollSummary']);
        Route::get('reports/attendance-summary', [\App\Http\Controllers\Api\HR\ReportController::class, 'attendanceSummary']);
        Route::get('reports/leave-summary', [\App\Http\Controllers\Api\HR\ReportController::class, 'leaveSummary']);
    });

    // ============================================
    // DASHBOARD & ANALYTICS
    // ============================================
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);
    Route::get('/dashboard/charts', [\App\Http\Controllers\Api\DashboardController::class, 'charts']);

    // ============================================
    // SETTINGS & ADMINISTRATION
    // ============================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
        Route::post('/users', [\App\Http\Controllers\Api\Admin\UserController::class, 'store']);
        Route::put('/users/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'update']);
        Route::delete('/users/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'destroy']);

        Route::get('/roles', [\App\Http\Controllers\Api\Admin\RoleController::class, 'index']);
        Route::post('/roles', [\App\Http\Controllers\Api\Admin\RoleController::class, 'store']);

        Route::get('/permissions', [\App\Http\Controllers\Api\Admin\PermissionController::class, 'index']);

        Route::get('/tenant', [\App\Http\Controllers\Api\Admin\TenantController::class, 'show']);
        Route::put('/tenant', [\App\Http\Controllers\Api\Admin\TenantController::class, 'update']);

        Route::get('/subscription', [\App\Http\Controllers\Api\Admin\SubscriptionController::class, 'show']);
    });
});
