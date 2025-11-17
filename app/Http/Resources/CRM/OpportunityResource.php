<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'value' => (float) $this->value,
            'probability' => $this->probability,
            'expected_close_date' => $this->expected_close_date?->format('Y-m-d'),
            'actual_close_date' => $this->actual_close_date?->format('Y-m-d'),
            'status' => $this->status,
            'lost_reason' => $this->lost_reason,
            'weighted_value' => (float) $this->value * ($this->probability / 100),

            // Relations
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'pipeline' => $this->whenLoaded('pipeline', fn() => [
                'id' => $this->pipeline->id,
                'name' => $this->pipeline->name,
            ]),
            'stage' => $this->whenLoaded('stage', fn() => [
                'id' => $this->stage->id,
                'name' => $this->stage->name,
                'probability' => $this->stage->probability,
            ]),
            'assigned_to' => $this->whenLoaded('assignedTo', fn() => [
                'id' => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
                'email' => $this->assignedTo->email,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
