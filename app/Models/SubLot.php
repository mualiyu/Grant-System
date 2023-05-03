<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubLot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category_id',
        'lot_id',
        // 'category',
        'program_id',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, "program_id", 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, "category_id", 'id');
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lot::class, "lot_id", 'id');
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(Application::class);
    }
}
