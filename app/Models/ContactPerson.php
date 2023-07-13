<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactPerson extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'app_prof_id',
        'name',
        'phone',
        'email',
        'address',
        'designation',
    ];

    public function app_profile(): BelongsTo
    {
        return $this->belongsTo(ApplicationProfile::class, "app_prof_id", 'id');
    }
}
