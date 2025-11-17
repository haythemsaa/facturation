<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'employee_number' => $this->employee_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'cin' => $this->cin,
            'cnss_number' => $this->cnss_number,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'children_count' => $this->children_count,
            'is_family_head' => (bool) $this->is_family_head,
            'nationality' => $this->nationality,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'status' => $this->status,
            'photo' => $this->photo,

            // Relations
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                    'code' => $this->department->code,
                ];
            }),

            'position' => $this->whenLoaded('position', function () {
                return [
                    'id' => $this->position->id,
                    'title' => $this->position->title,
                    'code' => $this->position->code,
                ];
            }),

            'active_contract' => $this->whenLoaded('activeContract'),
            'contracts' => $this->whenLoaded('contracts'),
            'payslips' => $this->whenLoaded('payslips'),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
