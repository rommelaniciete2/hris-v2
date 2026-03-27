<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'performance_review_cycle_id' => ['required', 'exists:performance_review_cycles,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'summary' => ['nullable', 'string'],
            'comments' => ['nullable', 'string'],
            'kpis' => ['required', 'array', 'min:1'],
            'kpis.*.name' => ['required', 'string', 'max:255'],
            'kpis.*.score' => ['required', 'integer', 'min:1', 'max:5'],
            'kpis.*.comments' => ['nullable', 'string'],
        ];
    }
}
