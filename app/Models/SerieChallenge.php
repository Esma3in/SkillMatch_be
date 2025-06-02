<?php

namespace App\Models;

use App\Models\Challenge;
use App\Models\Attestation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SerieChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'challenge',
        'number'
    ];

    public function attestations(): HasMany
    {
        return $this->hasMany(Attestation::class, 'series_challenge_id');
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }


}
