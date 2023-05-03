<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationFinancialDebtInfo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'project_name',
        'location',
        'sector',
        'aggregate_amount',
        'date_of_financial_close',
        // 'date_of_first_drawdown',
        // 'date_of_final_drawdown',
        // 'tenor_of_financing',
        'evidence_of_support ',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, "application_id", 'id');
    }

    public function borrowers(): HasMany
    {
        return $this->hasMany(ApplicationFinancialDebtInfoBorrower::class, "application_financial_debt_id", 'id');
    }
}
