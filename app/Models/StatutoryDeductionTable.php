<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\StatutoryDeductionTableFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutoryDeductionTable extends Model
{
    /** @use HasFactory<StatutoryDeductionTableFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'effective_from',
        'effective_to',
        'rules',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'rules' => 'array',
        ];
    }

    public function scopeActiveOn(Builder $query, CarbonInterface $date): Builder
    {
        return $query
            ->whereDate('effective_from', '<=', $date)
            ->where(function (Builder $nestedQuery) use ($date) {
                $nestedQuery
                    ->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            });
    }
}
