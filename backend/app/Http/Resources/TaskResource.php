<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
            'assignee' => $this->whenLoaded(
                'assignee',
                fn ($assignee) => UserResource::make($assignee),
            ),
        ];
    }
}
