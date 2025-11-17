<?php
namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Services\Compliance\PayrollCalculator;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    protected $payrollCalculator;

    public function __construct(PayrollCalculator $payrollCalculator)
    {
        $this->payrollCalculator = $payrollCalculator;
    }

    public function index(Request $request)
    {
        $query = Payslip::with('employee');

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->year) {
            $query->where('year', $request->year);
        }

        if ($request->month) {
            $query->where('month', $request->month);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $payslips = $query->latest('year')->latest('month')->paginate($request->per_page ?? 15);

        return response()->json($payslips);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'worked_days' => 'required|integer|min:0|max:31',
            'earnings' => 'nullable|array',
            'deductions' => 'nullable|array',
        ]);

        $employee = \App\Models\Employee::findOrFail($validated['employee_id']);

        if (!$employee->activeContract) {
            return response()->json([
                'message' => 'L\'employé n\'a pas de contrat actif'
            ], 422);
        }

        // Check if payslip already exists
        $exists = Payslip::where('employee_id', $validated['employee_id'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Un bulletin de paie existe déjà pour ce mois'
            ], 422);
        }

        $payslipData = $this->payrollCalculator->calculatePayslip([
            'base_salary' => $employee->activeContract->base_salary,
            'is_family_head' => $employee->is_family_head,
            'children_count' => $employee->children_count,
            'worked_days' => $validated['worked_days'],
            'earnings' => $validated['earnings'] ?? [],
            'deductions' => $validated['deductions'] ?? [],
        ]);

        $payslip = Payslip::create([
            'employee_id' => $validated['employee_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'worked_days' => $validated['worked_days'],
            'base_salary' => $payslipData['base_salary'],
            'gross_salary' => $payslipData['gross_salary'],
            'total_earnings' => $payslipData['total_earnings'],
            'total_deductions' => $payslipData['total_deductions'],
            'cnss_employee' => $payslipData['cnss_employee'],
            'cnss_employer' => $payslipData['cnss_employer'],
            'irpp' => $payslipData['irpp'],
            'css' => $payslipData['css'],
            'tfp' => $payslipData['tfp'],
            'foprolos' => $payslipData['foprolos'],
            'net_salary' => $payslipData['net_salary'],
            'status' => 'draft',
            'details' => $payslipData,
        ]);

        return response()->json($payslip->load('employee'), 201);
    }

    public function show(Payslip $payslip)
    {
        return response()->json($payslip->load('employee.department', 'employee.position', 'lines'));
    }

    public function destroy(Payslip $payslip)
    {
        if ($payslip->status !== 'draft') {
            return response()->json([
                'message' => 'Seuls les bulletins en brouillon peuvent être supprimés'
            ], 422);
        }

        $payslip->delete();
        return response()->json(['message' => 'Bulletin supprimé avec succès']);
    }
}
