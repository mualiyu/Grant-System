<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'applicant_id',
        'application_id',
        'name',
        'registration_date',
        'cac_number',
        'address',
        'description',
        'website',
        'owner',
        'authorised_personel',
        'evidence_of_equipment_ownership',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, "application_id", 'id');
    }

    public function contact_persons(): HasMany
    {
        return $this->hasMany(ContactPerson::class, "app_prof_id", 'id');
    }

    public function share_holders(): HasMany
    {
        return $this->hasMany(ShareHolder::class, "app_prof_id", 'id');
    }

}
