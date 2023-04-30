<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class, "program_id", 'id');
    }

    public function sublots(): HasMany
    {
        return $this->hasMany(SubLot::class, "program_id", 'id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProgramRequirement::class, "program_id", 'id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProgramDocument::class, "program_id", 'id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProgramStage::class, "program_id", 'id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(ProgramStatus::class, "program_id", 'id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, "program_id", 'id');
    }
}
