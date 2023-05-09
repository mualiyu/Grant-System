<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'program_id',
        'applicant_id',
        'from',
        'to',
        'msg',
        'type',
        'status',
        'file',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, "program_id", 'id');
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class, "appicant_id", 'id');
    }
}
