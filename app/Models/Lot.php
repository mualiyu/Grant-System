<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'region_id',
        'program_id',
        'category_id',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, "program_id", 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, "category_id", 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, "region_id", 'id');
    }

    public function sublots(): HasMany
    {
        return $this->hasMany(SubLot::class, "lot_id", 'id');
    }
}
