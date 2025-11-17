<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine if the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('hr')
            && $user->hasPermissionTo('view_employees');
    }

    /**
     * Determine if the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $user->tenant_id === $employee->tenant_id
            && ($user->hasPermissionTo('view_employees') || $user->id === $employee->user_id);
    }

    /**
     * Determine if the user can create employees.
     */
    public function create(User $user): bool
    {
        $subscription = $user->tenant->activeSubscription;

        return $subscription
            && $subscription->hasModule('hr')
            && $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine if the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        // Regular users cannot edit employee records
        return $user->tenant_id === $employee->tenant_id
            && $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine if the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        // Cannot delete if employee has payslips
        if ($employee->payslips()->exists()) {
            return false;
        }

        return $user->tenant_id === $employee->tenant_id
            && $user->hasRole('admin');
    }

    /**
     * Determine if the user can view salary information.
     */
    public function viewSalary(User $user, Employee $employee): bool
    {
        return $user->tenant_id === $employee->tenant_id
            && ($user->hasRole(['admin', 'manager']) || $user->id === $employee->user_id);
    }
}
