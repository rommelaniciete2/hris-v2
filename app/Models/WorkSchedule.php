<?php

namespace App\Models;

use Database\Factories\WorkScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    /** @use HasFactory<WorkScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'late_grace_minutes',
        'break_minutes',
        'weekdays',
        'is_flexible',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'late_grace_minutes' => 'integer',
            'break_minutes' => 'integer',
            'weekdays' => 'array',
            'is_flexible' => 'boolean',
        ];
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
