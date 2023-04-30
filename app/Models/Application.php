<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'applicant_id',
        'program_id',
    ];

    public function sublots(): BelongsToMany
    {
        return $this->belongsToMany(SubLot::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, "applicant_id", 'id');
    }

     public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, "program_id", 'id');
    }

    public function app_profiles(): HasOne
    {
        return $this->hasOne(ApplicationProfile::class, "application_id", 'id');
    }

    public function app_staffs(): HasMany
    {
        return $this->hasMany(ApplicationCv::class, "application_id", 'id');
    }

    public function app_projects(): HasMany
    {
        return $this->hasMany(ApplicationProject::class, "application_id", 'id');
    }

    public function app_financials(): HasMany
    {
        return $this->hasMany(ApplicationFinancialInfo::class, "application_id", 'id');
    }

    public function app_financial_depts(): HasMany
    {
        return $this->hasMany(ApplicationFinancialDebtInfo::class, "application_id", 'id');
    }
    

}
