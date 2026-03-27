<?php

namespace App\Http\Resources;

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
            'full_name' => $this->fullName(),
            'email' => $this->user?->email,
            'department' => $this->department?->name,
            'position' => $this->position?->name,
            'employment_status' => $this->employment_status,
            'base_salary' => $this->base_salary,
        ];
    }
}
