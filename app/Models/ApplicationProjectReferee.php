<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationProjectReferee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_project_id',
        'name',
        'phone',
    ];

    public function application_project(): BelongsTo
    {
        return $this->belongsTo(ApplicationProject::class, "application_project_id", 'id');
    }
}
