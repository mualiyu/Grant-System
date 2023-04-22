<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'isInitial',
        'color',
        'editable',
        'program_id',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, "program_id", 'id');
    }
}
