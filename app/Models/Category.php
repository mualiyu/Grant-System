<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class, "category_id", 'id');
    }

    public function sublots(): HasMany
    {
        return $this->hasMany(SubLot::class, "category_id", 'id');
    }
}
