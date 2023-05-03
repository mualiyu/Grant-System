<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicationCv extends Model
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
        // 'dob',
        'language',
        // 'nationality',
        // 'countries_experience',
        // 'work_undertaken',
        'education_certificate',
        'professional_certificate',
        'cv',
        'membership',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, "application_id", 'id');
    }

    // public function educations(): HasMany
    // {
    //     return $this->hasMany(ApplicationEducation::class, "application_cv_id", 'id');
    // }

    // public function memberships(): HasOne
    // {
    //     return $this->hasOne(ApplicationMembership::class, "application_cv_id", 'id');
    // }

    // public function trainings(): HasMany
    // {
    //     return $this->hasMany(ApplicationTraining::class, "application_cv_id", 'id');
    // }

    public function employers(): HasMany
    {
        return $this->hasMany(ApplicationEmployer::class, "application_cv_id", 'id');
    }

    public function current_position(): HasOne
    {
        return $this->hasOne(ApplicationCurrentPosition::class, "application_cv_id", 'id');
    }
}
