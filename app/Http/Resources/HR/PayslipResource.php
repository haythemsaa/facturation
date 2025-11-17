<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->month,
            'year' => $this->year,
            'payment_date' => $this->payment_date?->format('Y-m-d'),
            'status' => $this->status,

            // Salaries
            'base_salary' => (float) $this->base_salary,
            'gross_salary' => (float) $this->gross_salary,
            'total_earnings' => (float) $this->total_earnings,
            'total_deductions' => (float) $this->total_deductions,
            'net_salary' => (float) $this->net_salary,

            // Social contributions
            'cnss_employee' => (float) $this->cnss_employee,
            'cnss_employer' => (float) $this->cnss_employer,
            'irpp' => (float) $this->irpp,
            'css' => (float) $this->css,
            'tfp' => (float) $this->tfp,
            'foprolos' => (float) $this->foprolos,

            // Work time
            'worked_days' => $this->worked_days,
            'worked_hours' => $this->worked_hours ? (float) $this->worked_hours : null,
            'overtime_hours' => $this->overtime_hours ? (float) $this->overtime_hours : null,

            'details' => $this->details,

            // Relations
            'employee' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->id,
                    'employee_number' => $this->employee->employee_number,
                    'full_name' => $this->employee->first_name . ' ' . $this->employee->last_name,
                    'cin' => $this->employee->cin,
                    'cnss_number' => $this->employee->cnss_number,
                ];
            }),

            'lines' => $this->whenLoaded('lines'),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
