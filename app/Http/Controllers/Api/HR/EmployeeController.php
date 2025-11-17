<?php
namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('department', 'position', 'activeContract');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('employee_number', 'like', "%{$request->search}%")
                  ->orWhere('cin', 'like', "%{$request->search}%");
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($employees);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|unique:employees,employee_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'cin' => 'required|string|unique:employees,cin',
            'cnss_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'children_count' => 'nullable|integer|min:0',
            'is_family_head' => 'boolean',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'mobile' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'required|date',
            'status' => 'required|in:active,on_leave,suspended,terminated',
        ]);

        $employee = Employee::create($validated);

        return response()->json($employee->load('department', 'position'), 201);
    }

    public function show(Employee $employee)
    {
        return response()->json($employee->load([
            'department',
            'position',
            'activeContract',
            'contracts',
            'attendances' => function($q) {
                $q->latest('date')->limit(30);
            },
            'payslips' => function($q) {
                $q->latest('year')->latest('month')->limit(12);
            }
        ]));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'cnss_number' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'children_count' => 'nullable|integer|min:0',
            'is_family_head' => 'boolean',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'mobile' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'status' => 'sometimes|in:active,on_leave,suspended,terminated',
        ]);

        $employee->update($validated);

        return response()->json($employee->load('department', 'position'));
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(['message' => 'Employé supprimé avec succès']);
    }
}
