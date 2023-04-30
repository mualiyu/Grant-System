<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEducation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'application_cv_id',
        'qualification',
        'course',
        'school',
        'start',
        'end',
    ];

    public function application_cv(): BelongsTo
    {
        return $this->belongsTo(ApplicationCv::class, "application_cv_id", 'id');
    }
}
