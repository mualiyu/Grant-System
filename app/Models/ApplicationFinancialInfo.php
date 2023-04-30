<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationFinancialInfo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'total_assets',
        'total_liability',
        'total_networth',
        'annual_turnover',
        'profit_before_taxes',
        'profit_after_taxes',
        'type'
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, "application_id", 'id');
    }
}
