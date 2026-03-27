<?php

namespace App\Support\Hris;

use App\Models\StatutoryDeductionTable;
use Carbon\CarbonImmutable;

class StatutoryDeductionCalculator
{
    /**
     * @return array<string, float>
     */
    public function calculateSemiMonthly(float $monthlySalary, CarbonImmutable $asOfDate): array
    {
        $result = [];

        $tables = StatutoryDeductionTable::query()
            ->activeOn($asOfDate)
            ->orderByDesc('effective_from')
            ->get()
            ->unique('type');

        foreach ($tables as $table) {
            $rules = $table->rules['brackets'] ?? [];
            $amount = 0.0;

            foreach ($rules as $rule) {
                $minimum = (float) ($rule['min'] ?? 0);
                $maximum = array_key_exists('max', $rule) && $rule['max'] !== null
                    ? (float) $rule['max']
                    : null;

                if ($monthlySalary < $minimum) {
                    continue;
                }

                if ($maximum !== null && $monthlySalary > $maximum) {
                    continue;
                }

                if (isset($rule['employee_fixed'])) {
                    $amount = (float) $rule['employee_fixed'];
                } else {
                    $base = (float) ($rule['base'] ?? 0);
                    $over = (float) ($rule['over'] ?? $minimum);
                    $rate = (float) ($rule['employee_rate'] ?? $rule['rate'] ?? 0);
                    $amount = $base + max(0, $monthlySalary - $over) * $rate;
                }

                if (isset($rule['cap'])) {
                    $amount = min($amount, (float) $rule['cap']);
                }

                break;
            }

            $result[strtolower($table->type)] = round($amount / 2, 2);
        }

        return $result;
    }
}
