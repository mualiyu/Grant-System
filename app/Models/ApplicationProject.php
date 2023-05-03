<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationProject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_id',
        'name',
        'address',
        'date_of_contract',
        'employer',
        'location',
        'description',
        'date_of_completion',
        'project_cost',
        'role_of_applicant',
        // 'equity',
        // 'implemented',
        'geocoordinate',
        'subcontactor_role',
        'award_letter',
        'interim_valuation_cert',
        'certificate_of_completion',
        'evidence_of_completion',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, "application_id", 'id');
    }

    public function referees(): HasMany
    {
        return $this->hasMany(ApplicationProjectReferee::class, "application_project_id", 'id');
    }

    public function sub_contractors(): HasMany
    {
        return $this->hasMany(ApplicationProjectSubContractor::class, "application_project_id", 'id');
    }
}
