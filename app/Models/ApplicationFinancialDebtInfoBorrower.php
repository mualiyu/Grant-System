<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationFinancialDebtInfoBorrower extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_financial_debt_id',
        'name',
        'rc_number',
        'address',
    ];

    public function application_financial_debt(): BelongsTo
    {
        return $this->belongsTo(ApplicationFinancialDebtInfo::class, "application_financial_debt_id", 'id');
    }
}
