<?php

namespace App\Http\Resources\CRM;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'type' => $this->type,
            'description' => $this->description,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'is_completed' => (bool) $this->completed_at,

            // Relations
            'contact' => $this->whenLoaded('contact', fn() => [
                'id' => $this->contact->id,
                'name' => $this->contact->first_name . ' ' . $this->contact->last_name,
                'email' => $this->contact->email,
            ]),
            'opportunity' => $this->whenLoaded('opportunity', fn() => [
                'id' => $this->opportunity->id,
                'title' => $this->opportunity->title,
            ]),
            'performed_by' => $this->whenLoaded('performer', fn() => [
                'id' => $this->performer->id,
                'name' => $this->performer->name,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
