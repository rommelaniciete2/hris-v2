<?php

namespace App\Models;

use Database\Factories\PerformanceKpiScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceKpiScore extends Model
{
    /** @use HasFactory<PerformanceKpiScoreFactory> */
    use HasFactory;

    protected $fillable = [
        'performance_review_id',
        'name',
        'score',
        'comments',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function performanceReview(): BelongsTo
    {
        return $this->belongsTo(PerformanceReview::class);
    }
}
